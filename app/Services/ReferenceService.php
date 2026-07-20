<?php

namespace App\Services;

use Config\Database;

/**
 * Service de génération de références uniques de transaction.
 *
 * Format : MM + AAAAMMJJ + compteur journalier sur 6 chiffres.
 * Exemple : MM20260720000001
 */
class ReferenceService
{
    protected const PREFIX = 'MM';

    /**
     * Génère une référence de transaction unique, réutilisable dans tout le projet.
     */
    public function generate(): string
    {
        $db = Database::connect();

        $datePart = date('Ymd');

        $row = $db->query(
            "SELECT COUNT(*) AS nb FROM transactions WHERE date(created_at) = date('now')"
        )->getRowArray();

        $counter = (int) ($row['nb'] ?? 0) + 1;

        do {
            $sequence  = str_pad((string) $counter, 6, '0', STR_PAD_LEFT);
            $reference = self::PREFIX . $datePart . $sequence;

            $exists = $db->query(
                'SELECT 1 FROM transactions WHERE reference = ?',
                [$reference]
            )->getRowArray();

            $counter++;
        } while ($exists !== null);

        return $reference;
    }
}
