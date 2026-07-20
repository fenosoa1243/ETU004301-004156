<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FeeScaleModel;
use App\Models\OperationTypeModel;
use App\Services\FeeService;

class FeeScaleController extends BaseController
{
    protected FeeScaleModel $feeScaleModel;
    protected OperationTypeModel $operationTypeModel;
    protected FeeService $feeService;

    public function __construct()
    {
        $this->feeScaleModel      = new FeeScaleModel();
        $this->operationTypeModel = new OperationTypeModel();
        $this->feeService          = new FeeService();
    }

    public function index()
    {
        return view('admin/fee_scales/index', [
            'title'     => 'Barèmes des frais',
            'feeScales' => $this->feeScaleModel->getWithType(),
        ]);
    }

    public function create()
    {
        return view('admin/fee_scales/create', [
            'title' => 'Ajouter une tranche de frais',
            'types' => $this->operationTypeModel->where('applique_frais', 1)->findAll(),
        ]);
    }

    public function store()
    {
        $data = $this->validatedData();

        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($data['montant_min'] >= $data['montant_max']) {
            return redirect()->back()->withInput()->with('error', 'Le montant minimum doit être inférieur au montant maximum.');
        }

        if ($this->feeService->overlaps((int) $data['operation_type_id'], (float) $data['montant_min'], (float) $data['montant_max'])) {
            return redirect()->back()->withInput()->with('error', 'Cette tranche chevauche une tranche existante.');
        }

        $this->feeScaleModel->insert($data);

        return redirect()->to('admin/fee-scales')->with('success', 'Tranche de frais ajoutée avec succès.');
    }

    public function edit(int $id)
    {
        $feeScale = $this->feeScaleModel->find($id);

        if (! $feeScale) {
            return redirect()->to('admin/fee-scales')->with('error', 'Tranche introuvable.');
        }

        return view('admin/fee_scales/edit', [
            'title'    => 'Modifier la tranche de frais',
            'feeScale' => $feeScale,
            'types'    => $this->operationTypeModel->where('applique_frais', 1)->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $data = $this->validatedData();

        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($data['montant_min'] >= $data['montant_max']) {
            return redirect()->back()->withInput()->with('error', 'Le montant minimum doit être inférieur au montant maximum.');
        }

        if ($this->feeService->overlaps((int) $data['operation_type_id'], (float) $data['montant_min'], (float) $data['montant_max'], $id)) {
            return redirect()->back()->withInput()->with('error', 'Cette tranche chevauche une tranche existante.');
        }

        $this->feeScaleModel->update($id, $data);

        return redirect()->to('admin/fee-scales')->with('success', 'Tranche modifiée avec succès.');
    }

    public function delete(int $id)
    {
        $this->feeScaleModel->delete($id);

        return redirect()->to('admin/fee-scales')->with('success', 'Tranche supprimée avec succès.');
    }

    protected function validatedData(): ?array
    {
        $data = [
            'operation_type_id' => $this->request->getPost('operation_type_id'),
            'montant_min'       => $this->request->getPost('montant_min'),
            'montant_max'       => $this->request->getPost('montant_max'),
            'frais'             => $this->request->getPost('frais'),
        ];

        if (! $this->validateData($data, [
            'operation_type_id' => 'required|integer',
            'montant_min'       => 'required|decimal|greater_than[0]',
            'montant_max'       => 'required|decimal|greater_than[0]',
            'frais'             => 'required|decimal|greater_than_equal_to[0]',
        ])) {
            return null;
        }

        return $data;
    }
}