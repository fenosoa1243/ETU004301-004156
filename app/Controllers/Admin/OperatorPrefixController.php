<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperatorModel;
use App\Models\OperatorPrefixModel;

class OperatorPrefixController extends BaseController
{
    protected OperatorPrefixModel $prefixModel;
    protected OperatorModel $operatorModel;

    public function __construct()
    {
        $this->prefixModel   = new OperatorPrefixModel();
        $this->operatorModel = new OperatorModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->prefixModel->withOperator()->orderBy('operator_prefixes.id', 'DESC');

        if ($search) {
            $query->groupStart()
                ->like('operator_prefixes.prefix', $search)
                ->orLike('operators.nom', $search)
            ->groupEnd();
        }

        $prefixes = $query->paginate(10, 'default', $page);

        return view('admin/operator_prefixes/index', [
            'title'    => 'Préfixes des autres opérateurs',
            'prefixes' => $prefixes,
            'pager'    => $this->prefixModel->pager,
            'search'   => $search,
        ]);
    }

    public function create()
    {
        return view('admin/operator_prefixes/create', [
            'title'     => 'Ajouter un préfixe externe',
            'operators' => $this->operatorModel->getActive(),
        ]);
    }

    public function store()
    {
        $data = [
            'operator_id' => $this->request->getPost('operator_id'),
            'prefix'      => $this->request->getPost('prefix'),
            'actif'       => 1,
        ];

        if (! $this->prefixModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->prefixModel->errors());
        }

        return redirect()->to('admin/operator-prefixes')->with('success', 'Préfixe externe ajouté avec succès.');
    }

    public function edit(int $id)
    {
        $prefix = $this->prefixModel->find($id);

        if (! $prefix) {
            return redirect()->to('admin/operator-prefixes')->with('error', 'Préfixe introuvable.');
        }

        return view('admin/operator_prefixes/edit', [
            'title'     => 'Modifier le préfixe externe',
            'prefix'    => $prefix,
            'operators' => $this->operatorModel->getActive(),
        ]);
    }

    public function update(int $id)
    {
        $data = [
            'id'          => $id,
            'operator_id' => $this->request->getPost('operator_id'),
            'prefix'      => $this->request->getPost('prefix'),
        ];

        if (! $this->prefixModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->prefixModel->errors());
        }

        return redirect()->to('admin/operator-prefixes')->with('success', 'Préfixe externe modifié avec succès.');
    }

    public function toggle(int $id)
    {
        $this->prefixModel->toggle($id);

        return redirect()->to('admin/operator-prefixes')->with('success', 'Statut du préfixe mis à jour.');
    }

    public function delete(int $id)
    {
        $this->prefixModel->delete($id);

        return redirect()->to('admin/operator-prefixes')->with('success', 'Préfixe externe supprimé avec succès.');
    }
}
