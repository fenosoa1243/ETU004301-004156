<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Services\OperationService;
use RuntimeException;

class TransferController extends BaseController
{
    protected OperationService $operationService;

    public function __construct()
    {
        $this->operationService = new OperationService();
    }

    public function create()
    {
        $client = (new ClientModel())->find(session()->get('client_id'));

        return view('client/transfer/create', [
            'title'  => 'Transfert',
            'client' => $client,
        ]);
    }

    public function store()
    {
        $rules = [
            'telephone_destinataire' => 'required|numeric|exact_length[10]',
            'montant'                => 'required|numeric|greater_than[0]',
        ];

        $messages = [
            'telephone_destinataire' => [
                'required'     => 'Le numéro du destinataire est obligatoire.',
                'numeric'      => 'Le numéro du destinataire doit être numérique.',
                'exact_length' => 'Le numéro du destinataire doit contenir exactement 10 chiffres.',
            ],
            'montant' => [
                'required'     => 'Le montant est obligatoire.',
                'numeric'      => 'Le montant doit être numérique.',
                'greater_than' => 'Le montant doit être supérieur à zéro.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $destinataire = normalize_phone((string) $this->request->getPost('telephone_destinataire'));
        $montant      = (float) $this->request->getPost('montant');

        try {
            $transaction = $this->operationService->transfer(
                (int) session()->get('client_id'),
                $destinataire,
                $montant
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('client'))
            ->with('success', 'Transfert de ' . format_money($montant) . ' effectué avec succès. Référence : ' . $transaction['reference']);
    }
}
