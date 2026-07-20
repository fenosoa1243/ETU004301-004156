<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PrefixModel;

class PrefixController extends BaseController
{
    protected PrefixModel $prefixModel;

    public function __construct()
    {
        $this->prefixModel = new PrefixModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->prefixModel->orderBy('id', 'DESC');

        if ($search) {
            $query->like('prefix', $search);
        }

        $prefixes = $query->paginate(10, 'default', $page);

        return view('admin/prefixes/index', [
            'title'    => 'Gestion des préfixes',
            'prefixes' => $prefixes,
            'pager'    => $this->prefixModel->pager,
            'search'   => $search,
        ]);
    }

    public function create()
    {
        return view('admin/prefixes/create', ['title' => 'Ajouter un préfixe']);
    }

    public function store()
    {
        $data = [
            'prefix' => $this->request->getPost('prefix'),
            'actif'  => 1,
        ];

        if (! $this->prefixModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->prefixModel->errors());
        }

        return redirect()->to('admin/prefixes')->with('success', 'Préfixe ajouté avec succès.');
    }

    public function edit(int $id)
    {
        $prefix = $this->prefixModel->find($id);

        if (! $prefix) {
            return redirect()->to('admin/prefixes')->with('error', 'Préfixe introuvable.');
        }

        return view('admin/prefixes/edit', ['title' => 'Modifier le préfixe', 'prefix' => $prefix]);
    }

    public function update(int $id)
    {
        $data = ['id' => $id, 'prefix' => $this->request->getPost('prefix')];

        if (! $this->prefixModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->prefixModel->errors());
        }

        return redirect()->to('admin/prefixes')->with('success', 'Préfixe modifié avec succès.');
    }

    public function toggle(int $id)
    {
        $this->prefixModel->toggle($id);

        return redirect()->to('admin/prefixes')->with('success', 'Statut du préfixe mis à jour.');
    }

    public function delete(int $id)
    {
        $this->prefixModel->delete($id);

        return redirect()->to('admin/prefixes')->with('success', 'Préfixe supprimé avec succès.');
    }
}