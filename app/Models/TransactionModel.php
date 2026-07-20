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

    /**
     * Construit une requête paginable des opérations d'un client (envoyées
     * ou reçues), avec les libellés du type d'opération et les numéros
     * de l'expéditeur/destinataire, filtrable par recherche, type, dates
     * et montant.
     *
     * @param array{search?: ?string, type?: ?string, date_debut?: ?string, date_fin?: ?string, montant_min?: ?string, montant_max?: ?string} $filters
     */
    public function queryForClient(int $clientId, array $filters = []): self
    {
        $this->select('
                transactions.*,
                operation_types.nom AS type_operation,
                cs.telephone AS expediteur,
                cd.telephone AS destinataire
            ')
            ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
            ->join('clients cs', 'cs.id = transactions.client_source_id')
            ->join('clients cd', 'cd.id = transactions.client_destination_id', 'left')
            ->groupStart()
                ->where('transactions.client_source_id', $clientId)
                ->orWhere('transactions.client_destination_id', $clientId)
            ->groupEnd();

        if (! empty($filters['type'])) {
            $this->where('operation_types.nom', $filters['type']);
        }

        if (! empty($filters['date_debut'])) {
            $this->where('transactions.created_at >=', $filters['date_debut'] . ' 00:00:00');
        }

        if (! empty($filters['date_fin'])) {
            $this->where('transactions.created_at <=', $filters['date_fin'] . ' 23:59:59');
        }

        if (! empty($filters['montant_min'])) {
            $this->where('transactions.montant >=', $filters['montant_min']);
        }

        if (! empty($filters['montant_max'])) {
            $this->where('transactions.montant <=', $filters['montant_max']);
        }

        if (! empty($filters['search'])) {
            $this->groupStart()
                ->like('transactions.reference', $filters['search'])
                ->orLike('cs.telephone', $filters['search'])
                ->orLike('cd.telephone', $filters['search'])
            ->groupEnd();
        }

        return $this->orderBy('transactions.created_at', 'DESC');
    }
}
