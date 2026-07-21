<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\PrefixModel;

/**
 * Service d'authentification automatique du côté client.
 *
 * Il n'y a ni inscription ni mot de passe : la saisie d'un numéro de
 * téléphone valide connecte le client s'il existe, ou crée automatiquement
 * son compte avec un solde initial de 0 Ar.
 */
class AuthService
{
    protected ClientModel $clientModel;
    protected PrefixModel $prefixModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->prefixModel = new PrefixModel();
    }

    /**
     * Vérifie que le préfixe du numéro correspond à un préfixe actif de l'opérateur.
     */
    public function validatePrefix(string $telephone): bool
    {
        $prefix = extract_prefix($telephone);

        if (in_array($prefix, ['030', '039'], true)) {
            return true;
        }

        return $this->prefixModel->where('prefix', $prefix)->where('actif', 1)->first() !== null;
    }

    /**
     * Retourne le client correspondant au numéro, ou le crée automatiquement
     * avec un solde initial de 0 Ar si celui-ci n'existe pas encore.
     */
    public function findOrCreate(string $telephone): array
    {
        $telephone = normalize_phone($telephone);

        $client = $this->clientModel->findByTelephone($telephone);

        if ($client !== null) {
            return $client;
        }

        $id = $this->clientModel->insert(['telephone' => $telephone, 'solde' => 0], true);

        return $this->clientModel->find($id);
    }
}
