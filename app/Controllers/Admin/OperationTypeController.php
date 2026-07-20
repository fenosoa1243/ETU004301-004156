<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperationTypeModel;

class OperationTypeController extends BaseController
{
    protected OperationTypeModel $operationTypeModel;

    public function __construct()
    {
        $this->operationTypeModel = new OperationTypeModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->operationTypeModel->orderBy('id', 'ASC');

        if ($search) {
            $query->like('nom', $search);
        }

        $types = $query->paginate(10, 'default', $page);

        return view('admin/operation_types/index', [
            'title'  => "Types d'opérations",
            'types'  => $types,
            'pager'  => $this->operationTypeModel->pager,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin/operation_types/create', ['title' => "Ajouter un type d'opération"]);
    }

    public function store()
    {
        $data = [
            'nom'            => $this->request->getPost('nom'),
            'description'    => $this->request->getPost('description'),
            'applique_frais' => $this->request->getPost('applique_frais') ? 1 : 0,
        ];

        if (! $this->operationTypeModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->operationTypeModel->errors());
        }

        return redirect()->to('admin/operation-types')->with('success', "Type d'opération ajouté avec succès.");
    }

    public function edit(int $id)
    {
        $type = $this->operationTypeModel->find($id);

        if (! $type) {
            return redirect()->to('admin/operation-types')->with('error', 'Type introuvable.');
        }

        return view('admin/operation_types/edit', ['title' => 'Modifier le type', 'type' => $type]);
    }

    public function update(int $id)
    {
        $data = [
            'id'             => $id,
            'nom'            => $this->request->getPost('nom'),
            'description'    => $this->request->getPost('description'),
            'applique_frais' => $this->request->getPost('applique_frais') ? 1 : 0,
        ];

        if (! $this->operationTypeModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->operationTypeModel->errors());
        }

        return redirect()->to('admin/operation-types')->with('success', 'Type modifié avec succès.');
    }

    public function delete(int $id)
    {
        $this->operationTypeModel->delete($id);

        return redirect()->to('admin/operation-types')->with('success', 'Type supprimé avec succès.');
    }
}