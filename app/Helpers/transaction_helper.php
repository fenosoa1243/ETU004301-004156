<?php

/**
 * Helper d'affichage des transactions (icônes, badges, signe).
 */

if (! function_exists('transaction_icon')) {
    /**
     * Retourne la classe d'icône Bootstrap Icons associée à un type d'opération.
     */
    function transaction_icon(string $typeOperation): string
    {
        return match ($typeOperation) {
            'Dépôt'     => 'bi-arrow-down-circle text-success',
            'Retrait'   => 'bi-arrow-up-circle text-danger',
            'Transfert' => 'bi-arrow-left-right text-primary',
            default     => 'bi-circle text-muted',
        };
    }
}

if (! function_exists('transaction_badge')) {
    /**
     * Retourne la classe de badge Bootstrap associée à un type d'opération.
     */
    function transaction_badge(string $typeOperation): string
    {
        return match ($typeOperation) {
            'Dépôt'     => 'bg-success',
            'Retrait'   => 'bg-danger',
            'Transfert' => 'bg-primary',
            default     => 'bg-secondary',
        };
    }
}

if (! function_exists('transaction_sign')) {
    /**
     * Détermine si une opération est un débit ('-') ou un crédit ('+')
     * du point de vue du client connecté.
     */
    function transaction_sign(array $transaction, int $clientId): string
    {
        $type = $transaction['type_operation'] ?? null;

        if ($type === 'Dépôt') {
            return '+';
        }

        if ($type === 'Retrait') {
            return '-';
        }

        return (int) $transaction['client_source_id'] === $clientId ? '-' : '+';
    }
}
