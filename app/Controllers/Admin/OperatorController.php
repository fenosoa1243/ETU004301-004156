<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperatorModel;

class OperatorController extends BaseController
{
    protected OperatorModel $operatorModel;

    public function __construct()
    {
        $this->operatorModel = new OperatorModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->operatorModel->orderBy('id', 'DESC');

        if ($search) {
            $query->groupStart()
                ->like('nom', $search)
                ->orLike('code', $search)
            ->groupEnd();
        }

        $operators = $query->paginate(10, 'default', $page);

        return view('admin/operators/index', [
            'title'     => 'Opérateurs',
            'operators' => $operators,
            'pager'     => $this->operatorModel->pager,
            'search'    => $search,
        ]);
    }

    public function create()
    {
        return view('admin/operators/create', ['title' => 'Ajouter un opérateur']);
    }

    public function store()
    {
        $data = [
            'nom'  => $this->request->getPost('nom'),
            'code' => strtoupper((string) $this->request->getPost('code')),
            'actif' => 1,
        ];

        if (! $this->operatorModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->operatorModel->errors());
        }

        return redirect()->to('admin/operators')->with('success', 'Opérateur ajouté avec succès.');
    }

    public function edit(int $id)
    {
        $operator = $this->operatorModel->find($id);

        if (! $operator) {
            return redirect()->to('admin/operators')->with('error', 'Opérateur introuvable.');
        }

        return view('admin/operators/edit', ['title' => 'Modifier l\'opérateur', 'operator' => $operator]);
    }

    public function update(int $id)
    {
        $data = [
            'id'   => $id,
            'nom'  => $this->request->getPost('nom'),
            'code' => strtoupper((string) $this->request->getPost('code')),
        ];

        if (! $this->operatorModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->operatorModel->errors());
        }

        return redirect()->to('admin/operators')->with('success', 'Opérateur modifié avec succès.');
    }

    public function toggle(int $id)
    {
        $this->operatorModel->toggle($id);

        return redirect()->to('admin/operators')->with('success', 'Statut de l\'opérateur mis à jour.');
    }

    public function delete(int $id)
    {
        $this->operatorModel->delete($id);

        return redirect()->to('admin/operators')->with('success', 'Opérateur supprimé avec succès.');
    }
}
