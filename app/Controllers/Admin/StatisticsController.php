<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\StatisticsService;

class StatisticsController extends BaseController
{
    protected StatisticsService $statisticsService;

    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
    }

    public function gains()
    {
        $filters = [
            'date_debut' => $this->request->getGet('date_debut'),
            'date_fin'   => $this->request->getGet('date_fin'),
            'type'       => $this->request->getGet('type'),
        ];

        return view('admin/statistics/gains', [
            'title'   => 'Situation des gains',
            'gains'   => $this->statisticsService->getGains($filters),
            'filters' => $filters,
        ]);
    }

    public function advanced()
    {
        return view('admin/statistics/advanced', [
            'title' => 'Statistiques avancées',
            'stats' => $this->statisticsService->getAdvancedStats(),
        ]);
    }
}