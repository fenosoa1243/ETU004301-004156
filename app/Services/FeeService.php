<?php

namespace App\Services;

use App\Models\FeeScaleModel;
use App\Models\InterOperatorCommissionModel;
use RuntimeException;

class FeeService
{
    protected FeeScaleModel $feeScaleModel;
    protected InterOperatorCommissionModel $commissionModel;

    public function __construct()
    {
        $this->feeScaleModel    = new FeeScaleModel();
        $this->commissionModel  = new InterOperatorCommissionModel();
    }

    /**
     * Calcule les frais d'une opération (Version 1 compatible).
     *
     * @return array{montant: float, frais: float, montant_total: float}
     */
    public function calculateFee(int $operationTypeId, float $montant, bool $appliqueFrais = true): array
    {
        return $this->calculateDetailedFee($operationTypeId, $montant, false, $appliqueFrais, false);
    }

    /**
     * Calcule les frais détaillés, avec commission inter-opérateur si externe.
     *
     * @return array{
     *     montant: float,
     *     frais: float,
     *     frais_retrait: float,
     *     commission_supplementaire: float,
     *     montant_total: float,
     *     is_external: bool
     * }
     */
    public function calculateDetailedFee(
        int $operationTypeId,
        float $montant,
        bool $isExternal = false,
        bool $appliqueFrais = true,
        bool $inclureFraisRetrait = false
    ): array {
        if ($montant <= 0) {
            throw new RuntimeException('Le montant doit être supérieur à zéro.');
        }

        if (! $appliqueFrais) {
            return [
                'montant'                   => $montant,
                'frais'                     => 0.0,
                'frais_retrait'             => 0.0,
                'commission_supplementaire' => 0.0,
                'montant_total'             => $montant,
                'is_external'               => $isExternal,
            ];
        }

        $tranche = $this->feeScaleModel->findForAmount($operationTypeId, $montant);

        if ($tranche === null) {
            throw new RuntimeException('Aucun barème de frais ne correspond à ce montant.');
        }

        $frais                   = (float) $tranche['frais'];
        $fraisRetrait            = 0.0;
        $commissionSupplementaire = 0.0;

        if ($operationTypeId === OperationService::TRANSFERT && ! $isExternal && $inclureFraisRetrait) {
            $retraitTranche = $this->feeScaleModel->findForAmount(OperationService::RETRAIT, $montant);

            if ($retraitTranche === null) {
                throw new RuntimeException('Aucun barème de frais de retrait ne correspond à ce montant.');
            }

            $fraisRetrait = (float) $retraitTranche['frais'];
        }

        if ($isExternal) {
            $commissionSupplementaire = $this->calculateSupplementaryCommission($frais);
        }

        return [
            'montant'                   => $montant,
            'frais'                     => $frais,
            'frais_retrait'             => $fraisRetrait,
            'commission_supplementaire' => $commissionSupplementaire,
            'montant_total'             => $montant + $frais + $fraisRetrait + $commissionSupplementaire,
            'is_external'               => $isExternal,
        ];
    }

    /**
     * Commission supplémentaire = pourcentage actif appliqué sur la commission normale.
     */
    public function calculateSupplementaryCommission(float $frais): float
    {
        $active = $this->commissionModel->getActive();

        if ($active === null) {
            return 0.0;
        }

        $pourcentage = (float) $active['pourcentage'];

        return round($frais * ($pourcentage / 100), 2);
    }

    public function getActiveCommissionPercentage(): float
    {
        $active = $this->commissionModel->getActive();

        return $active !== null ? (float) $active['pourcentage'] : 0.0;
    }

    public function overlaps(int $operationTypeId, float $min, float $max, ?int $excludeId = null): bool
    {
        return $this->feeScaleModel->overlaps($operationTypeId, $min, $max, $excludeId);
    }
}
