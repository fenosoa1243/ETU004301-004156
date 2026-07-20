<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionHistoryModel extends Model
{
    protected $table            = 'commission_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['commission_id', 'pourcentage_avant', 'pourcentage_apres'];
    protected $useTimestamps    = false;

    public function forCommission(int $commissionId): array
    {
        return $this->where('commission_id', $commissionId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function allWithCommission(): array
    {
        return $this->select('commission_history.*, inter_operator_commissions.pourcentage AS pourcentage_actuel')
            ->join('inter_operator_commissions', 'inter_operator_commissions.id = commission_history.commission_id')
            ->orderBy('commission_history.created_at', 'DESC')
            ->findAll();
    }
}
