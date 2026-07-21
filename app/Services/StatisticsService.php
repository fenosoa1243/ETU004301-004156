<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\OperatorModel;
use App\Models\OperationTypeModel;
use App\Models\TransactionModel;
use Config\Database;

class StatisticsService
{
    protected TransactionModel $transactionModel;
    protected ClientModel $clientModel;
    protected OperationTypeModel $operationTypeModel;
    protected OperatorModel $operatorModel;

    protected const DEPOT     = 1;
    protected const RETRAIT   = 2;
    protected const TRANSFERT = 3;

    public function __construct()
    {
        $this->transactionModel   = new TransactionModel();
        $this->clientModel        = new ClientModel();
        $this->operationTypeModel = new OperationTypeModel();
        $this->operatorModel      = new OperatorModel();
    }

    public function getGlobalStats(): array
    {
        $db = Database::connect();

        $externalStats = $db->table('vue_gains_externes')->get()->getRowArray() ?? [];

        return [
            'nb_clients'                  => $this->clientModel->countAllResults(),
            'nb_depots'                 => $this->transactionModel->countByType(self::DEPOT),
            'nb_retraits'               => $this->transactionModel->countByType(self::RETRAIT),
            'nb_transferts'             => $this->transactionModel->countByType(self::TRANSFERT),
            'total_depots'              => $this->transactionModel->sumByType(self::DEPOT, 'montant'),
            'total_retraits'            => $this->transactionModel->sumByType(self::RETRAIT, 'montant'),
            'total_transferts'          => $this->transactionModel->sumByType(self::TRANSFERT, 'montant'),
            'revenu_frais'              => $this->transactionModel->sumByType(self::RETRAIT, 'frais')
                                            + $this->transactionModel->sumByType(self::TRANSFERT, 'frais')
                                            + $this->transactionModel->sumByType(self::TRANSFERT, 'frais_retrait'),
            'nb_operateurs'             => $this->operatorModel->countActive(),
            'nb_transferts_externes'    => (int) ($externalStats['nb_transferts'] ?? 0),
            'montant_transferts_externes' => (float) ($externalStats['total_montant'] ?? 0),
            'commission_sup_collectee'  => (float) ($externalStats['total_commission_sup'] ?? 0),
            'nb_transferts_internes'    => $this->transactionModel->countInternalTransfers(),
            'dernieres_operations'      => $this->transactionModel
                ->select('transactions.*, operation_types.nom AS type_operation, c1.telephone AS expediteur, COALESCE(c2.telephone, transactions.destination_telephone) AS destinataire, o.nom AS operateur_externe')
                ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
                ->join('clients c1', 'c1.id = transactions.client_source_id')
                ->join('clients c2', 'c2.id = transactions.client_destination_id', 'left')
                ->join('operators o', 'o.id = transactions.external_operator_id', 'left')
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

        $internes = $this->getInternalGains($filters);
        $externes = $this->getExternalGains($filters);

        // Total des frais de retrait : les retraits purs + les frais de
        // retrait optionnellement inclus dans un transfert interne.
        $retraitBuilder = $db->table('transactions')->where('operation_type_id', self::RETRAIT);
        $this->applyDateFilters($retraitBuilder, $filters);
        $totalFraisRetraitsPurs = array_sum(array_column($retraitBuilder->get()->getResultArray(), 'frais'));

        $transfertBuilder = $db->table('transactions')->where('operation_type_id', self::TRANSFERT);
        $this->applyDateFilters($transfertBuilder, $filters);
        $totalFraisRetraitViaTransfert = array_sum(array_column($transfertBuilder->get()->getResultArray(), 'frais_retrait'));

        $totalFraisRetraits   = $totalFraisRetraitsPurs + $totalFraisRetraitViaTransfert;
        $totalFraisTransferts = $internes['total_frais'] + $externes['total_frais'];
        $totalCommissionsExt  = $externes['total_commission_sup'];
        $totalGeneral         = $totalFraisRetraits + $totalFraisTransferts + $totalCommissionsExt;

        return [
            'par_type'      => $revenus,
            'details'       => $details,
            'revenu_total'  => array_sum(array_column($revenus, 'total_frais'))
                                + array_sum(array_column($revenus, 'total_commission_sup')),
            'nb_operations' => $nbOperations,
            'moyenne_frais' => $nbOperations > 0 ? $totalFrais / $nbOperations : 0,
            'internes'      => $internes,
            'externes'      => $externes,
            // Synthèse demandée par le cahier des charges :
            'total_frais_retraits'   => $totalFraisRetraits,
            'total_frais_transferts' => $totalFraisTransferts,
            'total_commissions_ext'  => $totalCommissionsExt,
            'total_general'          => $totalGeneral,
        ];
    }

    public function getInternalGains(array $filters = []): array
    {
        $db      = Database::connect();
        $builder = $db->table('transactions')
            ->where('operation_type_id', self::TRANSFERT)
            ->where('is_external', 0);

        $this->applyDateFilters($builder, $filters);

        $rows = $builder->get()->getResultArray();

        return [
            'nb_transferts' => count($rows),
            'total_frais'   => array_sum(array_column($rows, 'frais')),
            'total_montant' => array_sum(array_column($rows, 'montant')),
        ];
    }

    public function getExternalGains(array $filters = []): array
    {
        $db      = Database::connect();
        $builder = $db->table('transactions')
            ->where('operation_type_id', self::TRANSFERT)
            ->where('is_external', 1);

        $this->applyDateFilters($builder, $filters);

        $rows = $builder->get()->getResultArray();

        $totalFrais = array_sum(array_column($rows, 'frais'));
        $totalSup   = array_sum(array_column($rows, 'commission_supplementaire'));

        return [
            'nb_transferts'             => count($rows),
            'total_frais'               => $totalFrais,
            'total_commission_sup'      => $totalSup,
            'total_gains'               => $totalFrais + $totalSup,
            'total_montant'             => array_sum(array_column($rows, 'montant')),
        ];
    }

    public function getSettlementStats(array $filters = [], string $sort = 'operateur', string $order = 'ASC'): array
    {
        $db = Database::connect();

        $builder = $db->table('vue_montants_a_envoyer');

        if (! empty($filters['search'])) {
            $builder->like('operateur', $filters['search']);
        }

        $allowedSort = ['operateur', 'nb_transferts', 'montant_total', 'commission_percue', 'montant_net_a_envoyer', 'derniere_date'];
        $sort        = in_array($sort, $allowedSort, true) ? $sort : 'operateur';
        $order       = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $rows = $builder->orderBy($sort, $order)->get()->getResultArray();

        return [
            'lignes'              => $rows,
            'total_transferts'    => array_sum(array_column($rows, 'nb_transferts')),
            'total_montant'       => array_sum(array_column($rows, 'montant_total')),
            'total_commission'    => array_sum(array_column($rows, 'commission_percue')),
            'total_net_a_envoyer' => array_sum(array_column($rows, 'montant_net_a_envoyer')),
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

    protected function applyDateFilters($builder, array $filters): void
    {
        if (! empty($filters['date_debut'])) {
            $builder->where('created_at >=', $filters['date_debut'] . ' 00:00:00');
        }
        if (! empty($filters['date_fin'])) {
            $builder->where('created_at <=', $filters['date_fin'] . ' 23:59:59');
        }
    }
}
