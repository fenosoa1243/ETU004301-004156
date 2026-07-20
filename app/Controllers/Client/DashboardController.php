<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $clientId = (int) session()->get('client_id');

        $clientModel      = new ClientModel();
        $transactionModel = new TransactionModel();

        $client     = $clientModel->find($clientId);
        $operations = $transactionModel->forClient($clientId);

        $totalEnvoye = array_sum(array_map(
            static fn ($o) => (int) $o['client_source_id'] === $clientId ? (float) $o['montant'] : 0,
            $operations
        ));

        $totalRecu = array_sum(array_map(
            static fn ($o) => (int) ($o['client_destination_id'] ?? 0) === $clientId ? (float) $o['montant'] : 0,
            $operations
        ));

        return view('client/dashboard/index', [
            'title'                 => 'Tableau de bord',
            'client'                => $client,
            'nb_operations'         => count($operations),
            'total_envoye'          => $totalEnvoye,
            'total_recu'            => $totalRecu,
            'dernieres_operations'  => array_slice($operations, 0, 5),
        ]);
    }
}
