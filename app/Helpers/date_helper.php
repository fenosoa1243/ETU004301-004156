<?php

/**
 * Helper de formatage des dates en français.
 */

if (! function_exists('format_datetime_fr')) {
    /**
     * Formate une date SQLite ("Y-m-d H:i:s") en "d/m/Y H:i".
     */
    function format_datetime_fr(?string $datetime): string
    {
        if (! $datetime) {
            return '—';
        }

        $timestamp = strtotime($datetime);

        return $timestamp ? date('d/m/Y H:i', $timestamp) : $datetime;
    }
}

if (! function_exists('format_date_fr')) {
    /**
     * Formate une date SQLite en "d/m/Y".
     */
    function format_date_fr(?string $datetime): string
    {
        if (! $datetime) {
            return '—';
        }

        $timestamp = strtotime($datetime);

        return $timestamp ? date('d/m/Y', $timestamp) : $datetime;
    }
}
