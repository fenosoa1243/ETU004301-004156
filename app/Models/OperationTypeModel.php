<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationTypeModel extends Model
{
    protected $table            = 'operation_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nom', 'description', 'applique_frais'];
    protected $useTimestamps    = false;

    protected $validationRules = [
        'id'             => 'permit_empty|integer',
        'nom'            => 'required|min_length[2]|is_unique[operation_types.nom,id,{id}]',
        'applique_frais' => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nom' => [
            'required'  => "Le nom de l'opération est obligatoire.",
            'is_unique' => "Ce type d'opération existe déjà.",
        ],
    ];
}