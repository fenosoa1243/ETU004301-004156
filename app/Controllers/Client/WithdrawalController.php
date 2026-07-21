<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Services\OperationService;
use RuntimeException;

class WithdrawalController extends BaseController
{
    protected OperationService $operationService;

    public function __construct()
    {
        $this->operationService = new OperationService();
    }

    public function create()
    {
        $client = (new ClientModel())->find(session()->get('client_id'));

        return view('client/withdrawal/create', [
            'title'  => 'Retrait',
            'client' => $client,
        ]);
    }

    public function store()
    {
        $rules = ['montant' => 'required|numeric|greater_than[0]'];

        $messages = [
            'montant' => [
                'required'      => 'Le montant est obligatoire.',
                'numeric'       => 'Le montant doit être numérique.',
                'greater_than'  => 'Le montant doit être supérieur à zéro.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $montant = (float) $this->request->getPost('montant');

        try {
            $transaction = $this->operationService->withdraw((int) session()->get('client_id'), $montant);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('client'))
            ->with('success', 'Retrait de ' . format_money($montant) . ' effectué avec succès. Référence : ' . $transaction['reference']);
    }
}
