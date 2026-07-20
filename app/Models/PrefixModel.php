<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixModel extends Model
{
    protected $table            = 'prefixes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['prefix', 'actif'];
    protected $useTimestamps    = false;

    protected $validationRules = [
        'prefix' => 'required|exact_length[3]|numeric|is_unique[prefixes.prefix,id,{id}]',
    ];

    protected $validationMessages = [
        'prefix' => [
            'required'     => 'Le préfixe est obligatoire.',
            'exact_length' => 'Le préfixe doit contenir exactement 3 chiffres.',
            'numeric'      => 'Le préfixe doit être numérique.',
            'is_unique'    => 'Ce préfixe existe déjà.',
        ],
    ];

    public function toggle(int $id): bool
    {
        $prefix = $this->find($id);
        if (! $prefix) {
            return false;
        }

        return (bool) $this->update($id, ['actif' => $prefix['actif'] ? 0 : 1]);
    }

    public function getActive(): array
    {
        return $this->where('actif', 1)->findAll();
    }
}