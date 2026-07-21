<?php

namespace App\Services;

use App\Models\FeeScaleModel;
use App\Models\PromotionModel;
use RuntimeException;

class FeeService
{
    protected FeeScaleModel $feeScaleModel;

    public function __construct()
    {
        $this->feeScaleModel = new FeeScaleModel();
    }

    /**
     * Calcule les frais applicables pour une opération donnée.
     * La promotion (si active) est automatiquement appliquée
     * uniquement sur les transferts internes (type 3, is_external = 0).
     *
     * @return array{montant: float, frais: float, montant_total: float}
     */
    public function calculateFee(int $operationTypeId, float $montant, bool $appliqueFrais = true, bool $isExternal = false): array
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

        // Promotion applicable uniquement sur les transferts internes
        if ($operationTypeId === 3 && ! $isExternal) {
            $frais = $this->applyInternalPromotion($frais);
        }

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

    protected function applyInternalPromotion(float $frais): float
    {
        $promotionModel = new PromotionModel();
        $active         = $promotionModel->getActive();

        if ($active === null) {
            return $frais;
        }

        return round($frais * (1 - ((float) $active['pourcentage'] / 100)), 2);
    }
}