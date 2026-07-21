<?php

namespace App\Models;

use CodeIgniter\Model;

class InterOperatorCommissionModel extends Model
{
    protected $table            = 'inter_operator_commissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['pourcentage', 'actif'];
    protected $useTimestamps    = false;

    protected $validationRules = [
        'pourcentage' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];

    protected $validationMessages = [
        'pourcentage' => [
            'required'              => 'Le pourcentage est obligatoire.',
            'numeric'               => 'Le pourcentage doit être numérique.',
            'greater_than_equal_to' => 'Le pourcentage doit être entre 0 % et 100 %.',
            'less_than_equal_to'    => 'Le pourcentage doit être entre 0 % et 100 %.',
        ],
    ];

    public function getActive(): ?array
    {
        return $this->where('actif', 1)->orderBy('id', 'DESC')->first();
    }

    public function deactivateAll(): void
    {
        $this->where('actif', 1)->set(['actif' => 0])->update();
    }
}
