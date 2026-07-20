<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\StatisticsService;

class SettlementController extends BaseController
{
    protected StatisticsService $statisticsService;

    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
        ];
        $sort  = (string) ($this->request->getGet('sort') ?? 'operateur');
        $order = (string) ($this->request->getGet('order') ?? 'ASC');

        $stats = $this->statisticsService->getSettlementStats($filters, $sort, $order);

        return view('admin/settlements/index', [
            'title'   => 'Montants à envoyer aux autres opérateurs',
            'stats'   => $stats,
            'filters' => $filters,
            'sort'    => $sort,
            'order'   => $order,
        ]);
    }
}
