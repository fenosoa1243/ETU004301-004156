<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use Config\Database;
use RuntimeException;

/**
 * Service métier des opérations client : Dépôt, Retrait, Transfert.
 *
 * Toutes les opérations sont exécutées dans une transaction SQLite.
 * La mise à jour effective des soldes est déléguée au trigger SQL
 * `tg_transactions_update_soldes` (déclenché après l'insertion d'une
 * transaction) : ce service se charge de calculer et vérifier les
 * montants, frais et soldes avant/après, puis d'insérer la transaction.
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

    public function __construct()
    {
        $this->clientModel      = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->feeService       = new FeeService();
        $this->referenceService = new ReferenceService();
        $this->authService      = new AuthService();
    }

    /**
     * Effectue un dépôt sur le compte du client. Aucun frais n'est appliqué.
     */
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

            $reference = $this->referenceService->generate();

            $this->transactionModel->insert([
                'reference'             => $reference,
                'operation_type_id'     => self::DEPOT,
                'client_source_id'      => $clientId,
                'client_destination_id' => null,
                'montant'               => $montant,
                'frais'                 => 0,
                'montant_total'         => $montant,
                'solde_avant'           => $soldeAvant,
                'solde_apres'           => $soldeApres,
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

    /**
     * Effectue un retrait sur le compte du client. Les frais sont calculés
     * automatiquement via le FeeService selon le barème de l'opérateur.
     */
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
                'reference'             => $reference,
                'operation_type_id'     => self::RETRAIT,
                'client_source_id'      => $clientId,
                'client_destination_id' => null,
                'montant'               => $montant,
                'frais'                 => $fee['frais'],
                'montant_total'         => $montantTotal,
                'solde_avant'           => $soldeAvant,
                'solde_apres'           => $soldeApres,
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
     * Effectue un transfert entre le client connecté et un numéro destinataire.
     * Le destinataire est créé automatiquement s'il n'existe pas encore.
     */
    public function transfer(int $sourceId, string $destinationTelephone, float $montant): array
    {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        $destinationTelephone = normalize_phone($destinationTelephone);

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

            if (! $this->authService->validatePrefix($destinationTelephone)) {
                throw new RuntimeException("Le préfixe du numéro destinataire n'est pas autorisé.");
            }

            $soldeAvant   = (float) $source['solde'];
            $fee          = $this->feeService->calculateFee(self::TRANSFERT, $montant, true);
            $montantTotal = $fee['montant_total'];
            $soldeApres   = $soldeAvant - $montantTotal;

            if ($soldeApres < 0) {
                throw new RuntimeException('Solde insuffisant pour effectuer ce transfert.');
            }

            $destination = $this->authService->findOrCreate($destinationTelephone);

            if ((int) $destination['id'] === $sourceId) {
                throw new RuntimeException('Impossible de transférer vers son propre numéro.');
            }

            $reference = $this->referenceService->generate();

            $this->transactionModel->insert([
                'reference'             => $reference,
                'operation_type_id'     => self::TRANSFERT,
                'client_source_id'      => $sourceId,
                'client_destination_id' => $destination['id'],
                'montant'               => $montant,
                'frais'                 => $fee['frais'],
                'montant_total'         => $montantTotal,
                'solde_avant'           => $soldeAvant,
                'solde_apres'           => $soldeApres,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue lors du transfert.');
        }

        return $this->transactionModel->where('reference', $reference)->first();
    }
}
