<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'reference', 'operation_type_id', 'client_source_id', 'client_destination_id',
        'montant', 'frais', 'montant_total', 'solde_avant', 'solde_apres',
    ];
    protected $useTimestamps = false;

    public function countByType(int $operationTypeId): int
    {
        return $this->where('operation_type_id', $operationTypeId)->countAllResults();
    }

    public function sumByType(int $operationTypeId, string $column = 'montant'): float
    {
        $result = $this->selectSum($column)
            ->where('operation_type_id', $operationTypeId)
            ->first();

        return (float) ($result[$column] ?? 0);
    }

    public function forClient(int $clientId): array
    {
        return $this->groupStart()
                ->where('client_source_id', $clientId)
                ->orWhere('client_destination_id', $clientId)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function latest(int $limit = 10): array
    {
        return $this->orderBy('created_at', 'DESC')->findAll($limit);
    }
}