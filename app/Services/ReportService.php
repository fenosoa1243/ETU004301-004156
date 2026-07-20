<?php

namespace App\Services;

use Config\Database;

class ReportService
{
    protected StatisticsService $statisticsService;

    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
    }

    public function generate(array $filters = []): array
    {
        $db = Database::connect();

        $transferBuilder = $db->table('transactions')
            ->where('operation_type_id', OperationService::TRANSFERT);

        $this->applyFilters($transferBuilder, $filters);

        $transferts = $transferBuilder->get()->getResultArray();

        $internes = array_filter($transferts, static fn ($t) => (int) $t['is_external'] === 0);
        $externes = array_filter($transferts, static fn ($t) => (int) $t['is_external'] === 1);

        $repartitionTransferts = [
            ['label' => 'Internes', 'nombre' => count($internes), 'montant' => array_sum(array_column($internes, 'montant'))],
            ['label' => 'Externes', 'nombre' => count($externes), 'montant' => array_sum(array_column($externes, 'montant'))],
        ];

        $revenusInternes = array_sum(array_column($internes, 'frais'));
        $revenusExternes = array_sum(array_column($externes, 'frais')) + array_sum(array_column($externes, 'commission_supplementaire'));

        $repartitionRevenus = [
            ['label' => 'Frais internes', 'montant' => $revenusInternes],
            ['label' => 'Frais + commission externe', 'montant' => $revenusExternes],
        ];

        $montantsDus = $db->table('vue_montants_a_envoyer')
            ->orderBy('montant_net_a_envoyer', 'DESC')
            ->get()->getResultArray();

        $topOperateurs = $db->table('transactions')
            ->select('operators.nom AS operateur, COUNT(transactions.id) AS nb, SUM(transactions.montant) AS montant, SUM(transactions.frais + transactions.commission_supplementaire) AS revenus')
            ->join('operators', 'operators.id = transactions.external_operator_id')
            ->where('transactions.is_external', 1)
            ->where('transactions.operation_type_id', OperationService::TRANSFERT)
            ->groupBy('operators.id')
            ->orderBy('montant', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return [
            'filters'                => $filters,
            'repartition_transferts' => $repartitionTransferts,
            'repartition_revenus'    => $repartitionRevenus,
            'montants_dus'           => $montantsDus,
            'top_operateurs'         => $topOperateurs,
            'totaux'                 => [
                'nb_transferts'     => count($transferts),
                'montant_total'     => array_sum(array_column($transferts, 'montant')),
                'revenu_total'      => $revenusInternes + $revenusExternes,
                'montant_du_total'  => array_sum(array_column($montantsDus, 'montant_net_a_envoyer')),
            ],
            'export' => [
                'pdf'   => 'admin/reports/export/pdf',
                'excel' => 'admin/reports/export/excel',
            ],
        ];
    }

    protected function applyFilters($builder, array $filters): void
    {
        if (! empty($filters['date_debut'])) {
            $builder->where('created_at >=', $filters['date_debut'] . ' 00:00:00');
        }
        if (! empty($filters['date_fin'])) {
            $builder->where('created_at <=', $filters['date_fin'] . ' 23:59:59');
        }
        if (! empty($filters['mois'])) {
            $builder->where("strftime('%Y-%m', created_at) =", $filters['mois']);
        }
        if (! empty($filters['annee'])) {
            $builder->where("strftime('%Y', created_at) =", $filters['annee']);
        }
    }
}
