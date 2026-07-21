<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\TransactionModel;

class BalanceController extends BaseController
{
    public function index()
    {
        $clientId = (int) session()->get('client_id');

        $clientModel      = new ClientModel();
        $transactionModel = new TransactionModel();

        $client     = $clientModel->find($clientId);
        $operations = $transactionModel->forClient($clientId);

        return view('client/balance/index', [
            'title'               => 'Mon solde',
            'client'              => $client,
            'nb_operations'       => count($operations),
            'derniere_operation'  => $operations[0] ?? null,
        ]);
    }
}
