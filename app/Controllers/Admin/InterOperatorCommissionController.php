<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommissionHistoryModel;
use App\Models\InterOperatorCommissionModel;

class InterOperatorCommissionController extends BaseController
{
    protected InterOperatorCommissionModel $commissionModel;
    protected CommissionHistoryModel $historyModel;

    public function __construct()
    {
        $this->commissionModel = new InterOperatorCommissionModel();
        $this->historyModel    = new CommissionHistoryModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->commissionModel->orderBy('id', 'DESC');

        if ($search) {
            $query->like('pourcentage', $search);
        }

        $commissions = $query->paginate(10, 'default', $page);
        $history     = $this->historyModel->allWithCommission();

        return view('admin/commissions/index', [
            'title'       => 'Commission inter-opérateur',
            'commissions' => $commissions,
            'history'     => $history,
            'pager'       => $this->commissionModel->pager,
            'search'      => $search,
            'active'      => $this->commissionModel->getActive(),
        ]);
    }

    public function create()
    {
        return view('admin/commissions/create', ['title' => 'Ajouter une commission']);
    }

    public function store()
    {
        $pourcentage = (float) $this->request->getPost('pourcentage');

        if ($this->request->getPost('actif')) {
            $this->commissionModel->deactivateAll();
        }

        $data = [
            'pourcentage' => $pourcentage,
            'actif'       => $this->request->getPost('actif') ? 1 : 0,
        ];

        if (! $this->commissionModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->commissionModel->errors());
        }

        return redirect()->to('admin/commissions')->with('success', 'Commission ajoutée avec succès.');
    }

    public function edit(int $id)
    {
        $commission = $this->commissionModel->find($id);

        if (! $commission) {
            return redirect()->to('admin/commissions')->with('error', 'Commission introuvable.');
        }

        return view('admin/commissions/edit', [
            'title'      => 'Modifier la commission',
            'commission' => $commission,
            'history'    => $this->historyModel->forCommission($id),
        ]);
    }

    public function update(int $id)
    {
        $commission = $this->commissionModel->find($id);

        if (! $commission) {
            return redirect()->to('admin/commissions')->with('error', 'Commission introuvable.');
        }

        $newPourcentage = (float) $this->request->getPost('pourcentage');
        $oldPourcentage = (float) $commission['pourcentage'];

        if ($this->request->getPost('actif')) {
            $this->commissionModel->deactivateAll();
        }

        $data = [
            'id'          => $id,
            'pourcentage' => $newPourcentage,
            'actif'       => $this->request->getPost('actif') ? 1 : 0,
        ];

        if (! $this->commissionModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->commissionModel->errors());
        }

        if ($oldPourcentage !== $newPourcentage) {
            $this->historyModel->insert([
                'commission_id'     => $id,
                'pourcentage_avant' => $oldPourcentage,
                'pourcentage_apres' => $newPourcentage,
            ]);
        }

        return redirect()->to('admin/commissions')->with('success', 'Commission modifiée avec succès.');
    }

    public function toggle(int $id)
    {
        $commission = $this->commissionModel->find($id);

        if (! $commission) {
            return redirect()->to('admin/commissions')->with('error', 'Commission introuvable.');
        }

        if (! $commission['actif']) {
            $this->commissionModel->deactivateAll();
            $this->commissionModel->update($id, ['actif' => 1]);
        } else {
            $this->commissionModel->update($id, ['actif' => 0]);
        }

        return redirect()->to('admin/commissions')->with('success', 'Statut de la commission mis à jour.');
    }

    public function delete(int $id)
    {
        $this->commissionModel->delete($id);

        return redirect()->to('admin/commissions')->with('success', 'Commission supprimée avec succès.');
    }
}
