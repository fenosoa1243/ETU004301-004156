<?php

namespace App\Models;

use CodeIgniter\Model;

class OperatorPrefixModel extends Model
{
    protected $table            = 'operator_prefixes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['operator_id', 'prefix', 'actif'];
    protected $useTimestamps    = false;

    protected $validationRules = [
        'id'          => 'permit_empty|integer',
        'operator_id' => 'required|integer',
        'prefix'      => 'required|exact_length[3]|numeric|is_unique[operator_prefixes.prefix,id,{id}]',
    ];

    protected $validationMessages = [
        'operator_id' => [
            'required' => 'L\'opérateur est obligatoire.',
        ],
        'prefix' => [
            'required'     => 'Le préfixe est obligatoire.',
            'exact_length' => 'Le préfixe doit contenir exactement 3 chiffres.',
            'numeric'      => 'Le préfixe doit être numérique.',
            'is_unique'    => 'Ce préfixe est déjà attribué à un opérateur.',
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

    public function findByPrefix(string $prefix): ?array
    {
        return $this->select('operator_prefixes.*, operators.nom AS operator_nom, operators.code AS operator_code')
            ->join('operators', 'operators.id = operator_prefixes.operator_id')
            ->where('operator_prefixes.prefix', $prefix)
            ->where('operator_prefixes.actif', 1)
            ->where('operators.actif', 1)
            ->first();
    }

    public function withOperator(): self
    {
        return $this->select('operator_prefixes.*, operators.nom AS operator_nom, operators.code AS operator_code')
            ->join('operators', 'operators.id = operator_prefixes.operator_id');
    }
}
