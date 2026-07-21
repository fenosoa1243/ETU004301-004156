<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionHistoryModel extends Model
{
    protected $table            = 'promotion_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['promotion_id', 'pourcentage_avant', 'pourcentage_apres'];
    protected $useTimestamps    = false;

    public function allWithPromotion(): array
    {
        return $this->select('promotion_history.*, internal_promotions.pourcentage AS pourcentage_actuel')
            ->join('internal_promotions', 'internal_promotions.id = promotion_history.promotion_id')
            ->orderBy('promotion_history.created_at', 'DESC')
            ->findAll();
    }

    public function forPromotion(int $promotionId): array
    {
        return $this->where('promotion_id', $promotionId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}