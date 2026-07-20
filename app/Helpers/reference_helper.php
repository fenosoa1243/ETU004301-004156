<?php

use App\Services\ReferenceService;

/**
 * Helper de génération de références de transaction.
 */

if (! function_exists('generate_reference')) {
    /**
     * Génère une référence unique de transaction (ex: MM20260720000001).
     */
    function generate_reference(): string
    {
        return (new ReferenceService())->generate();
    }
}
