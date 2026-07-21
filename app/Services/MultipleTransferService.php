<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use Config\Database;
use RuntimeException;

/**
 * Service pour gérer les transferts multiples (uniquement intra-opérateur).
 * Version 2 : Tous les bénéficiaires doivent appartenir au même opérateur que l'expéditeur.
 */
class MultipleTransferService
{
    public const TRANSFERT_MULTIPLE = 3; // Même ID que transfert simple

    protected ClientModel $clientModel;
    protected TransactionModel $transactionModel;
    protected FeeService $feeService;
    protected ReferenceService $referenceService;
    protected OperatorDetectionService $detectionService;
    protected AuthService $authService;

    public function __construct()
    {
        $this->clientModel      = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->feeService       = new FeeService();
        $this->referenceService = new ReferenceService();
        $this->detectionService = new OperatorDetectionService();
        $this->authService      = new AuthService();
    }

    /**
     * Valide la liste des bénéficiaires.
     *
     * @param int $sourceClientId ID client source
     * @param array $beneficiaires Liste de numéros à transférer
     * @return array{valid: bool, errors: array, operators: array}
     */
    public function validate(int $sourceClientId, array $beneficiaires): array
    {
        $errors = [];
        $operators = [];

        // Récupérer l'opérateur du source
        $source = $this->clientModel->find($sourceClientId);
        if ($source === null) {
            return [
                'valid' => false,
                'errors' => ['Client source introuvable.'],
                'operators' => [],
            ];
        }

        $sourceOperator = $this->detectionService->detect($source['telephone']);

        // Vérifications basiques
        if (count($beneficiaires) < 2) {
            $errors[] = 'Au minimum 2 bénéficiaires sont requis pour un transfert multiple.';
        }

        if (count(array_unique($beneficiaires)) !== count($beneficiaires)) {
            $errors[] = 'Des numéros en doublon ont été détectés.';
        }

        if (count($errors) > 0) {
            return [
                'valid' => false,
                'errors' => $errors,
                'operators' => [],
            ];
        }

        // Vérifier chaque bénéficiaire
        foreach ($beneficiaires as $tel) {
            $tel = normalize_phone($tel);

            // Auto-transfert ?
            if ($tel === $source['telephone']) {
                $errors[] = "Impossible de transférer vers son propre numéro ({$tel}).";
                continue;
            }

            // Détecte l'opérateur
            try {
                $detection = $this->detectionService->detect($tel);
                $operators[$tel] = $detection;

                // Opérateur différent ?
                if ($detection['operator_id'] !== $sourceOperator['operator_id']) {
                    $errors[] = "Le numéro {$tel} appartient à un autre opérateur. "
                        . "Les transferts multiples ne sont autorisés que vers les numéros du même opérateur.";
                }
            } catch (RuntimeException $e) {
                $errors[] = "Erreur détection {$tel}: " . $e->getMessage();
            }
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'operators' => $operators,
        ];
    }

    /**
     * Effectue un transfert multiple (tous vers le même opérateur).
     *
     * @param int $sourceClientId ID du client source
     * @param array $beneficiaires Liste de numéros
     * @param float $montantParBeneficiaire Montant par bénéficiaire
     * @return array{
     *     success: bool,
     *     reference_batch: string,
     *     montant_total: float,
     *     nombre_beneficiaires: int,
     *     transactions: array,
     *     errors: array
     * }
     */
    public function transfer(
        int $sourceClientId,
        array $beneficiaires,
        float $montantParBeneficiaire
    ): array {
        if ($montantParBeneficiaire <= 0) {
            throw new RuntimeException('Le montant par bénéficiaire doit être supérieur à zéro.');
        }

        // Validation préalable
        $validation = $this->validate($sourceClientId, $beneficiaires);
        if (! $validation['valid']) {
            throw new RuntimeException('Validation échouée : ' . implode('; ', $validation['errors']));
        }

        $db = Database::connect();
        $db->transStart();

        try {
            $source = $this->clientModel->find($sourceClientId);
            if ($source === null) {
                throw new RuntimeException('Client introuvable.');
            }

            // Calculer le montant total à débiter
            $fee = $this->feeService->calculateDetailedFee(
                OperationService::TRANSFERT,
                $montantParBeneficiaire,
                false,  // isExternal (toujours false pour transferts multiples)
                true,   // appliqueFrais
                false   // inclureFraisRetrait (pas d'option pour transferts multiples)
            );

            $montantTotal = $fee['montant_total'] * count($beneficiaires);
            $soldeAvant = (float)$source['solde'];
            $soldeApres = $soldeAvant - $montantTotal;

            if ($soldeApres < 0) {
                throw new RuntimeException(
                    'Solde insuffisant. Nécessaire : ' . format_money($montantTotal)
                    . ', Disponible : ' . format_money($soldeAvant)
                );
            }

            $referenceBatch = $this->referenceService->generate();
            $transactions = [];
            $soldeCourant = $soldeAvant;

            // Créer une transaction par bénéficiaire
            foreach ($beneficiaires as $tel) {
                $tel = normalize_phone($tel);
                $destination = $this->authService->findOrCreate($tel);
                $destinationClientId = (int)$destination['id'];

                $reference = $this->referenceService->generate();
                $soldeAvantLigne = $soldeCourant;
                $soldeApresLigne = $soldeCourant - $fee['montant_total'];
                $soldeCourant    = $soldeApresLigne;

                $this->transactionModel->insert([
                    'reference' => $reference,
                    'operation_type_id' => OperationService::TRANSFERT,
                    'client_source_id' => $sourceClientId,
                    'client_destination_id' => $destinationClientId,
                    'destination_telephone' => null,
                    'montant' => $montantParBeneficiaire,
                    'frais' => $fee['frais'],
                    'frais_retrait' => 0,
                    'commission_supplementaire' => 0,
                    'montant_total' => $fee['montant_total'],
                    'is_external' => 0,
                    'external_operator_id' => null,
                    'solde_avant' => $soldeAvantLigne,
                    'solde_apres' => $soldeApresLigne,
                    'batch_reference' => $referenceBatch,
                ]);

                $transactions[] = $this->transactionModel->where('reference', $reference)->first();
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue lors du transfert multiple.');
        }

        return [
            'success' => true,
            'reference_batch' => $referenceBatch,
            'montant_total' => $montantTotal,
            'nombre_beneficiaires' => count($beneficiaires),
            'transactions' => $transactions,
            'errors' => [],
        ];
    }
}
