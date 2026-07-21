<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use Config\Database;
use RuntimeException;

/**
 * Service métier des opérations client : Dépôt, Retrait, Transfert.
 */
class OperationService
{
    public const DEPOT     = 1;
    public const RETRAIT   = 2;
    public const TRANSFERT = 3;

    protected ClientModel $clientModel;
    protected TransactionModel $transactionModel;
    protected FeeService $feeService;
    protected ReferenceService $referenceService;
    protected AuthService $authService;
    protected OperatorDetectionService $detectionService;

    public function __construct()
    {
        $this->clientModel      = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->feeService       = new FeeService();
        $this->referenceService = new ReferenceService();
        $this->authService      = new AuthService();
        $this->detectionService = new OperatorDetectionService();
    }

    public function deposit(int $clientId, float $montant): array
    {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        $db = Database::connect();
        $db->transStart();

        try {
            $client = $this->clientModel->find($clientId);

            if ($client === null) {
                throw new RuntimeException('Client introuvable.');
            }

            $soldeAvant = (float) $client['solde'];
            $soldeApres = $soldeAvant + $montant;
            $reference  = $this->referenceService->generate();

            $this->transactionModel->insert([
                'reference'                 => $reference,
                'operation_type_id'         => self::DEPOT,
                'client_source_id'          => $clientId,
                'client_destination_id'     => null,
                'destination_telephone'     => null,
                'montant'                   => $montant,
                'frais'                     => 0,
                'commission_supplementaire' => 0,
                'montant_total'             => $montant,
                'is_external'               => 0,
                'external_operator_id'      => null,
                'solde_avant'               => $soldeAvant,
                'solde_apres'               => $soldeApres,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue lors du dépôt.');
        }

        return $this->transactionModel->where('reference', $reference)->first();
    }

    public function withdraw(int $clientId, float $montant): array
    {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        $db = Database::connect();
        $db->transStart();

        try {
            $client = $this->clientModel->find($clientId);

            if ($client === null) {
                throw new RuntimeException('Client introuvable.');
            }

            $soldeAvant   = (float) $client['solde'];
            $fee          = $this->feeService->calculateFee(self::RETRAIT, $montant, true);
            $montantTotal = $fee['montant_total'];
            $soldeApres   = $soldeAvant - $montantTotal;

            if ($soldeApres < 0) {
                throw new RuntimeException('Solde insuffisant pour effectuer ce retrait.');
            }

            $reference = $this->referenceService->generate();

            $this->transactionModel->insert([
                'reference'                 => $reference,
                'operation_type_id'         => self::RETRAIT,
                'client_source_id'          => $clientId,
                'client_destination_id'     => null,
                'destination_telephone'     => null,
                'montant'                   => $montant,
                'frais'                     => $fee['frais'],
                'commission_supplementaire' => 0,
                'montant_total'             => $montantTotal,
                'is_external'               => 0,
                'external_operator_id'      => null,
                'solde_avant'               => $soldeAvant,
                'solde_apres'               => $soldeApres,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue lors du retrait.');
        }

        return $this->transactionModel->where('reference', $reference)->first();
    }

    /**
     * Transfert interne ou inter-opérateur selon détection automatique.
     */
    public function transfer(int $sourceId, string $destinationTelephone, float $montant, bool $inclureFraisRetrait = false): array
    {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        $destinationTelephone = normalize_phone($destinationTelephone);
        $detection            = $this->detectionService->detect($destinationTelephone);

        $db = Database::connect();
        $db->transStart();

        try {
            $source = $this->clientModel->find($sourceId);

            if ($source === null) {
                throw new RuntimeException('Client introuvable.');
            }

            if ($destinationTelephone === $source['telephone']) {
                throw new RuntimeException('Impossible de transférer vers son propre numéro.');
            }

            $isExternal = ! $detection['is_internal'];
            $fee        = $this->feeService->calculateDetailedFee(self::TRANSFERT, $montant, $isExternal, true, $inclureFraisRetrait);

            $soldeAvant   = (float) $source['solde'];
            $montantTotal = $fee['montant_total'];
            $soldeApres   = $soldeAvant - $montantTotal;

            if ($soldeApres < 0) {
                throw new RuntimeException('Solde insuffisant pour effectuer ce transfert.');
            }

            $reference           = $this->referenceService->generate();
            $destinationClientId = null;

            if ($detection['is_internal']) {
                $destination         = $this->authService->findOrCreate($destinationTelephone);
                $destinationClientId = (int) $destination['id'];

                if ($destinationClientId === $sourceId) {
                    throw new RuntimeException('Impossible de transférer vers son propre numéro.');
                }
            }

            $this->transactionModel->insert([
                'reference'                 => $reference,
                'operation_type_id'         => self::TRANSFERT,
                'client_source_id'          => $sourceId,
                'client_destination_id'     => $destinationClientId,
                'destination_telephone'     => $isExternal ? $destinationTelephone : null,
                'montant'                   => $montant,
                'frais'                     => $fee['frais'],
                'frais_retrait'             => $fee['frais_retrait'],
                'commission_supplementaire' => $fee['commission_supplementaire'],
                'montant_total'             => $montantTotal,
                'is_external'               => $isExternal ? 1 : 0,
                'external_operator_id'      => $isExternal ? $detection['operator_id'] : null,
                'solde_avant'               => $soldeAvant,
                'solde_apres'               => $soldeApres,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue lors du transfert.');
        }

        $transaction = $this->transactionModel->where('reference', $reference)->first();
        $transaction['detection'] = $detection;

        return $transaction;
    }

    /**
     * Prévisualise les frais d'un transfert (interne ou externe).
     */
    public function previewTransfer(string $destinationTelephone, float $montant, bool $inclureFraisRetrait = false): array
    {
        $destinationTelephone = normalize_phone($destinationTelephone);
        $detection            = $this->detectionService->detect($destinationTelephone);
        $isExternal           = ! $detection['is_internal'];
        $fee                  = $this->feeService->calculateDetailedFee(self::TRANSFERT, $montant, $isExternal, true, $inclureFraisRetrait);

        return array_merge($fee, [
            'detection' => $detection,
        ]);
    }
}
