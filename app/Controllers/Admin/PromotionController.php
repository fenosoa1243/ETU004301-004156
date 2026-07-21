<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromotionHistoryModel;
use App\Models\PromotionModel;

class PromotionController extends BaseController
{
    protected PromotionModel $promotionModel;
    protected PromotionHistoryModel $historyModel;

    public function __construct()
    {
        $this->promotionModel = new PromotionModel();
        $this->historyModel   = new PromotionHistoryModel();
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $query = $this->promotionModel->orderBy('id', 'DESC');

        if ($search) {
            $query->like('pourcentage', $search);
        }

        $promotions = $query->paginate(10, 'default', $page);
        $history    = $this->historyModel->allWithPromotion();

        return view('admin/promotions/index', [
            'title'      => 'Promotion sur les frais de transfert interne',
            'promotions' => $promotions,
            'history'    => $history,
            'pager'      => $this->promotionModel->pager,
            'search'     => $search,
            'active'     => $this->promotionModel->getActive(),
        ]);
    }

    public function create()
    {
        return view('admin/promotions/create', ['title' => 'Ajouter une promotion']);
    }

    public function store()
    {
        if ($this->request->getPost('actif')) {
            $this->promotionModel->deactivateAll();
        }

        $data = [
            'pourcentage' => (float) $this->request->getPost('pourcentage'),
            'actif'       => $this->request->getPost('actif') ? 1 : 0,
        ];

        if (! $this->promotionModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->promotionModel->errors());
        }

        return redirect()->to('admin/promotions')->with('success', 'Promotion ajoutée avec succès.');
    }

    public function edit(int $id)
    {
        $promotion = $this->promotionModel->find($id);

        if (! $promotion) {
            return redirect()->to('admin/promotions')->with('error', 'Promotion introuvable.');
        }

        return view('admin/promotions/edit', [
            'title'     => 'Modifier la promotion',
            'promotion' => $promotion,
            'history'   => $this->historyModel->forPromotion($id),
        ]);
    }

    public function update(int $id)
    {
        $promotion = $this->promotionModel->find($id);

        if (! $promotion) {
            return redirect()->to('admin/promotions')->with('error', 'Promotion introuvable.');
        }

        $newPourcentage = (float) $this->request->getPost('pourcentage');
        $oldPourcentage = (float) $promotion['pourcentage'];

        if ($this->request->getPost('actif')) {
            $this->promotionModel->deactivateAll();
        }

        $data = [
            'id'          => $id,
            'pourcentage' => $newPourcentage,
            'actif'       => $this->request->getPost('actif') ? 1 : 0,
        ];

        if (! $this->promotionModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->promotionModel->errors());
        }

        if ($oldPourcentage !== $newPourcentage) {
            $this->historyModel->insert([
                'promotion_id'      => $id,
                'pourcentage_avant' => $oldPourcentage,
                'pourcentage_apres' => $newPourcentage,
            ]);
        }

        return redirect()->to('admin/promotions')->with('success', 'Promotion modifiée avec succès.');
    }

    public function toggle(int $id)
    {
        $promotion = $this->promotionModel->find($id);

        if (! $promotion) {
            return redirect()->to('admin/promotions')->with('error', 'Promotion introuvable.');
        }

        if (! $promotion['actif']) {
            $this->promotionModel->deactivateAll();
            $this->promotionModel->update($id, ['actif' => 1]);
        } else {
            $this->promotionModel->update($id, ['actif' => 0]);
        }

        return redirect()->to('admin/promotions')->with('success', 'Statut de la promotion mis à jour.');
    }

    public function delete(int $id)
    {
        $this->promotionModel->delete($id);

        return redirect()->to('admin/promotions')->with('success', 'Promotion supprimée avec succès.');
    }
}