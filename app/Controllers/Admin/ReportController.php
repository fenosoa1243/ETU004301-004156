<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ReportService;

class ReportController extends BaseController
{
    protected ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function index()
    {
        $filters = [
            'date_debut' => $this->request->getGet('date_debut'),
            'date_fin'   => $this->request->getGet('date_fin'),
            'mois'       => $this->request->getGet('mois'),
            'annee'      => $this->request->getGet('annee'),
        ];

        $report = $this->reportService->generate($filters);

        return view('admin/reports/index', [
            'title'  => 'Rapports',
            'report' => $report,
        ]);
    }

    public function exportPdf()
    {
        return redirect()->back()->with('error', 'Export PDF : fonctionnalité prévue, à implémenter prochainement.');
    }

    public function exportExcel()
    {
        return redirect()->back()->with('error', 'Export Excel : fonctionnalité prévue, à implémenter prochainement.');
    }
}
