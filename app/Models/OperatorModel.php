<?php

namespace App\Models;

use CodeIgniter\Model;

class OperatorModel extends Model
{
    protected $table            = 'operators';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nom', 'code', 'actif'];
    protected $useTimestamps    = false;

    protected $validationRules = [
        'id'   => 'permit_empty|integer',
        'nom'  => 'required|min_length[2]|max_length[100]|is_unique[operators.nom,id,{id}]',
        'code' => 'required|alpha_numeric_punct|min_length[2]|max_length[20]|is_unique[operators.code,id,{id}]',
    ];

    protected $validationMessages = [
        'nom' => [
            'required'   => 'Le nom de l\'opérateur est obligatoire.',
            'min_length' => 'Le nom doit contenir au moins 2 caractères.',
            'is_unique'  => 'Ce nom d\'opérateur existe déjà.',
        ],
        'code' => [
            'required'   => 'Le code est obligatoire.',
            'is_unique'  => 'Ce code existe déjà.',
        ],
    ];

    public function toggle(int $id): bool
    {
        $operator = $this->find($id);
        if (! $operator) {
            return false;
        }

        return (bool) $this->update($id, ['actif' => $operator['actif'] ? 0 : 1]);
    }

    public function getActive(): array
    {
        return $this->where('actif', 1)->orderBy('nom', 'ASC')->findAll();
    }

    public function countActive(): int
    {
        return $this->where('actif', 1)->countAllResults();
    }
}
