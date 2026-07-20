<?php

namespace App\Validation;

use App\Services\MultipleTransferService;

/**
 * Règles de validation personnalisées pour les transferts multiples.
 */
class MultipleTransferRules
{
    protected MultipleTransferService $multipleTransferService;

    public function __construct()
    {
        $this->multipleTransferService = new MultipleTransferService();
    }

    /**
     * Valide que les bénéficiaires forment une liste valide.
     * 
     * Format : "0391234567,0391234568,0391234569"
     */
    public function validate_beneficiaires_list(string $value, string &$error = null): bool
    {
        if (empty($value)) {
            $error = 'La liste des bénéficiaires est obligatoire.';
            return false;
        }

        $beneficiaires = array_filter(array_map('trim', explode(',', $value)));

        if (count($beneficiaires) < 2) {
            $error = 'Au minimum 2 bénéficiaires sont requis pour un transfert multiple.';
            return false;
        }

        if (count($beneficiaires) > 50) {
            $error = 'Maximum 50 bénéficiaires autorisés par transfert.';
            return false;
        }

        // Vérifier les doublons
        if (count(array_unique($beneficiaires)) !== count($beneficiaires)) {
            $error = 'Des numéros en doublon ont été détectés.';
            return false;
        }

        // Vérifier format de chaque numéro
        foreach ($beneficiaires as $tel) {
            $tel = normalize_phone($tel);
            if (strlen($tel) !== 10 || !ctype_digit($tel)) {
                $error = "Format invalide : {$tel}";
                return false;
            }
        }

        return true;
    }

    /**
     * Valide que le montant est numérique et > 0.
     */
    public function validate_montant_multiple(string $value, string &$error = null): bool
    {
        if (empty($value)) {
            $error = 'Le montant par bénéficiaire est obligatoire.';
            return false;
        }

        if (!is_numeric($value) || (float)$value <= 0) {
            $error = 'Le montant doit être un nombre positif.';
            return false;
        }

        return true;
    }

    /**
     * Valide qu'aucun numéro n'est le numéro propre du client.
     * 
     * Usage : validate_no_self_transfer[{client_telephone}]
     */
    public function validate_no_self_transfer(string $value, string $field, array $data): bool
    {
        // Récupérer le paramètre "client_telephone" depuis $data
        $clientTelephone = $data['client_telephone'] ?? null;
        
        if ($clientTelephone === null) {
            return true; // Pas de vérification possible
        }

        $beneficiaires = array_filter(array_map('trim', explode(',', $value)));

        foreach ($beneficiaires as $tel) {
            $tel = normalize_phone($tel);
            if ($tel === normalize_phone($clientTelephone)) {
                return false; // Self-transfer détecté
            }
        }

        return true;
    }

    /**
     * Valide que tous les bénéficiaires appartiennent au même opérateur (intra-opérateur).
     * 
     * Usage : validate_same_operator_only[{client_id}]
     */
    public function validate_same_operator_only(string $value, string $field, array $data): bool
    {
        $clientId = $data['client_id'] ?? null;
        
        if ($clientId === null) {
            return true; // Pas de vérification possible
        }

        $beneficiaires = array_filter(array_map('trim', explode(',', $value)));
        
        $validation = $this->multipleTransferService->validate((int)$clientId, $beneficiaires);

        return $validation['valid'];
    }
}
