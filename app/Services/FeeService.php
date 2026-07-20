<?php

namespace App\Services;

use App\Models\FeeScaleModel;
use RuntimeException;

class FeeService
{
    protected FeeScaleModel $feeScaleModel;

    public function __construct()
    {
        $this->feeScaleModel = new FeeScaleModel();
    }

    /**
     * @return array{montant: float, frais: float, montant_total: float}
     */
    public function calculateFee(int $operationTypeId, float $montant, bool $appliqueFrais = true): array
    {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        if (! $appliqueFrais) {
            return ['montant' => $montant, 'frais' => 0.0, 'montant_total' => $montant];
        }

        $tranche = $this->feeScaleModel->findForAmount($operationTypeId, $montant);

        if ($tranche === null) {
            throw new RuntimeException('Aucun barème de frais ne correspond à ce montant.');
        }

        $frais = (float) $tranche['frais'];

        return [
            'montant'       => $montant,
            'frais'         => $frais,
            'montant_total' => $montant + $frais,
        ];
    }

    public function overlaps(int $operationTypeId, float $min, float $max, ?int $excludeId = null): bool
    {
        return $this->feeScaleModel->overlaps($operationTypeId, $min, $max, $excludeId);
    }
}