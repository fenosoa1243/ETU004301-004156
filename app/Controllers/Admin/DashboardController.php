<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\StatisticsService;

class DashboardController extends BaseController
{
    public function index()
    {
        $statisticsService = new StatisticsService();

        return view('admin/dashboard/index', [
            'title' => 'Tableau de bord',
            'stats' => $statisticsService->getGlobalStats(),
        ]);
    }
}