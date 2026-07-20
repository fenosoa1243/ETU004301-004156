<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeScaleModel extends Model
{
    protected $table            = 'fee_scales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['operation_type_id', 'montant_min', 'montant_max', 'frais'];
    protected $useTimestamps    = false;

    public function findForAmount(int $operationTypeId, float $montant): ?array
    {
        return $this->where('operation_type_id', $operationTypeId)
            ->where('montant_min <=', $montant)
            ->where('montant_max >=', $montant)
            ->first();
    }

    public function overlaps(int $operationTypeId, float $min, float $max, ?int $excludeId = null): bool
    {
        $builder = $this->where('operation_type_id', $operationTypeId)
            ->groupStart()
                ->where('montant_min <=', $max)
                ->where('montant_max >=', $min)
            ->groupEnd();

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getWithType(): array
    {
        return $this->select('fee_scales.*, operation_types.nom AS operation_nom')
            ->join('operation_types', 'operation_types.id = fee_scales.operation_type_id')
            ->orderBy('operation_types.nom', 'ASC')
            ->orderBy('fee_scales.montant_min', 'ASC')
            ->findAll();
    }
}