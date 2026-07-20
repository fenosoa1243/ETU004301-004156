<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\OperationTypeModel;
use App\Models\TransactionModel;
use Config\Database;

class StatisticsService
{
    protected TransactionModel $transactionModel;
    protected ClientModel $clientModel;
    protected OperationTypeModel $operationTypeModel;

    protected const DEPOT     = 1;
    protected const RETRAIT   = 2;
    protected const TRANSFERT = 3;

    public function __construct()
    {
        $this->transactionModel   = new TransactionModel();
        $this->clientModel        = new ClientModel();
        $this->operationTypeModel = new OperationTypeModel();
    }

    public function getGlobalStats(): array
    {
        return [
            'nb_clients'       => $this->clientModel->countAllResults(),
            'nb_depots'        => $this->transactionModel->countByType(self::DEPOT),
            'nb_retraits'      => $this->transactionModel->countByType(self::RETRAIT),
            'nb_transferts'    => $this->transactionModel->countByType(self::TRANSFERT),
            'total_depots'     => $this->transactionModel->sumByType(self::DEPOT, 'montant'),
            'total_retraits'   => $this->transactionModel->sumByType(self::RETRAIT, 'montant'),
            'total_transferts' => $this->transactionModel->sumByType(self::TRANSFERT, 'montant'),
            'revenu_frais'     => $this->transactionModel->sumByType(self::RETRAIT, 'frais')
                                    + $this->transactionModel->sumByType(self::TRANSFERT, 'frais'),
            'dernieres_operations' => $this->transactionModel
                ->select('transactions.*, operation_types.nom AS type_operation, c1.telephone AS expediteur, c2.telephone AS destinataire')
                ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
                ->join('clients c1', 'c1.id = transactions.client_source_id')
                ->join('clients c2', 'c2.id = transactions.client_destination_id', 'left')
                ->orderBy('transactions.created_at', 'DESC')
                ->findAll(10),
        ];
    }

    public function getGains(array $filters = []): array
    {
        $db = Database::connect();

        $revenus = $db->table('vue_revenus_operateur')->get()->getResultArray();

        $detailBuilder = $db->table('vue_historique')
            ->whereIn('type_operation', ['Retrait', 'Transfert']);

        if (! empty($filters['date_debut'])) {
            $detailBuilder->where('date_operation >=', $filters['date_debut'] . ' 00:00:00');
        }
        if (! empty($filters['date_fin'])) {
            $detailBuilder->where('date_operation <=', $filters['date_fin'] . ' 23:59:59');
        }
        if (! empty($filters['type'])) {
            $detailBuilder->where('type_operation', $filters['type']);
        }

        $details      = $detailBuilder->orderBy('date_operation', 'DESC')->get()->getResultArray();
        $totalFrais   = array_sum(array_column($details, 'frais'));
        $nbOperations = count($details);

        return [
            'par_type'      => $revenus,
            'details'       => $details,
            'revenu_total'  => array_sum(array_column($revenus, 'total_frais')),
            'nb_operations' => $nbOperations,
            'moyenne_frais' => $nbOperations > 0 ? $totalFrais / $nbOperations : 0,
        ];
    }

    public function getAdvancedStats(): array
    {
        $db = Database::connect();

        $parJour = $db->table('transactions')
            ->select("date(created_at) AS jour, COUNT(*) AS nombre")
            ->groupBy('jour')->orderBy('jour', 'DESC')->limit(14)
            ->get()->getResultArray();

        $parMois = $db->table('transactions')
            ->select("strftime('%Y-%m', created_at) AS mois, COUNT(*) AS nombre")
            ->groupBy('mois')->orderBy('mois', 'DESC')->limit(12)
            ->get()->getResultArray();

        $parType = $db->table('transactions')
            ->select('operation_types.nom AS type_operation, COUNT(*) AS nombre, SUM(transactions.montant) AS total_montant, SUM(transactions.frais) AS total_frais')
            ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
            ->groupBy('operation_types.nom')
            ->get()->getResultArray();

        $topClients = $db->table('transactions')
            ->select('clients.telephone, clients.nom, COUNT(*) AS nb_operations, SUM(transactions.montant) AS volume')
            ->join('clients', 'clients.id = transactions.client_source_id')
            ->groupBy('clients.id')->orderBy('volume', 'DESC')->limit(5)
            ->get()->getResultArray();

        return [
            'par_jour'    => array_reverse($parJour),
            'par_mois'    => array_reverse($parMois),
            'par_type'    => $parType,
            'top_clients' => $topClients,
        ];
    }
}