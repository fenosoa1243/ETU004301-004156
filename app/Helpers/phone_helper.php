<?php

/**
 * Helper de manipulation des numéros de téléphone.
 */

if (! function_exists('normalize_phone')) {
    /**
     * Supprime tous les espaces d'un numéro de téléphone.
     */
    function normalize_phone(string $telephone): string
    {
        return preg_replace('/\s+/', '', trim($telephone)) ?? '';
    }
}

if (! function_exists('format_phone')) {
    /**
     * Formate un numéro de 10 chiffres en groupes lisibles : "034 12 345 67".
     */
    function format_phone(?string $telephone): string
    {
        if ($telephone === null || strlen($telephone) !== 10) {
            return (string) $telephone;
        }

        return substr($telephone, 0, 3) . ' ' . substr($telephone, 3, 2) . ' '
            . substr($telephone, 5, 3) . ' ' . substr($telephone, 8, 2);
    }
}

if (! function_exists('extract_prefix')) {
    /**
     * Extrait les 3 premiers chiffres (préfixe opérateur) d'un numéro.
     */
    function extract_prefix(string $telephone): string
    {
        return substr($telephone, 0, 3);
    }
}
