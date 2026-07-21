<?php

/**
 * Helper de formatage monétaire (Ariary).
 */

if (! function_exists('format_money')) {
    /**
     * Formate un montant en Ariary : "12 500 Ar".
     */
    function format_money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' Ar';
    }
}

if (! function_exists('format_amount')) {
    /**
     * Formate un montant sans le suffixe "Ar" : "12 500".
     */
    function format_amount(float $amount): string
    {
        return number_format($amount, 0, ',', ' ');
    }
}
