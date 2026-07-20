<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class HistoryController extends BaseController
{
    protected TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $clientId = (int) session()->get('client_id');

        $filters = [
            'search'      => $this->request->getGet('search'),
            'type'        => $this->request->getGet('type'),
            'date_debut'  => $this->request->getGet('date_debut'),
            'date_fin'    => $this->request->getGet('date_fin'),
            'montant_min' => $this->request->getGet('montant_min'),
            'montant_max' => $this->request->getGet('montant_max'),
        ];

        $page = (int) ($this->request->getGet('page') ?? 1);

        $operations = $this->transactionModel
            ->queryForClient($clientId, $filters)
            ->paginate(10, 'default', $page);

        return view('client/history/index', [
            'title'      => 'Historique',
            'operations' => $operations,
            'pager'      => $this->transactionModel->pager,
            'filters'    => $filters,
            'client_id'  => $clientId,
        ]);
    }

    public function show(int $id)
    {
        $clientId = (int) session()->get('client_id');

        $operation = $this->transactionModel
            ->queryForClient($clientId)
            ->where('transactions.id', $id)
            ->first();

        if ($operation === null) {
            return redirect()->to(site_url('client/historique'))->with('error', 'Transaction introuvable.');
        }

        return view('client/history/show', [
            'title'     => 'Détail de la transaction',
            'operation' => $operation,
            'client_id' => $clientId,
        ]);
    }
}
