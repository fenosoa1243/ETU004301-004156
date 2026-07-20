<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['telephone', 'nom', 'solde'];
    protected $useTimestamps    = false;

    public function findByTelephone(string $telephone): ?array
    {
        return $this->where('telephone', $telephone)->first();
    }
}