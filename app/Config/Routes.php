<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Client\AuthController::login');

$routes->group('admin', static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index', ['as' => 'admin.dashboard']);

    $routes->get('prefixes', 'Admin\PrefixController::index', ['as' => 'admin.prefixes.index']);
    $routes->get('prefixes/create', 'Admin\PrefixController::create', ['as' => 'admin.prefixes.create']);
    $routes->post('prefixes', 'Admin\PrefixController::store', ['as' => 'admin.prefixes.store']);
    $routes->get('prefixes/(:num)/edit', 'Admin\PrefixController::edit/$1', ['as' => 'admin.prefixes.edit']);
    $routes->post('prefixes/(:num)', 'Admin\PrefixController::update/$1', ['as' => 'admin.prefixes.update']);
    $routes->post('prefixes/(:num)/toggle', 'Admin\PrefixController::toggle/$1', ['as' => 'admin.prefixes.toggle']);
    $routes->post('prefixes/(:num)/delete', 'Admin\PrefixController::delete/$1', ['as' => 'admin.prefixes.delete']);

    $routes->get('operation-types', 'Admin\OperationTypeController::index', ['as' => 'admin.operationTypes.index']);
    $routes->get('operation-types/create', 'Admin\OperationTypeController::create', ['as' => 'admin.operationTypes.create']);
    $routes->post('operation-types', 'Admin\OperationTypeController::store', ['as' => 'admin.operationTypes.store']);
    $routes->get('operation-types/(:num)/edit', 'Admin\OperationTypeController::edit/$1', ['as' => 'admin.operationTypes.edit']);
    $routes->post('operation-types/(:num)', 'Admin\OperationTypeController::update/$1', ['as' => 'admin.operationTypes.update']);
    $routes->post('operation-types/(:num)/delete', 'Admin\OperationTypeController::delete/$1', ['as' => 'admin.operationTypes.delete']);

    $routes->get('fee-scales', 'Admin\FeeScaleController::index', ['as' => 'admin.feeScales.index']);
    $routes->get('fee-scales/create', 'Admin\FeeScaleController::create', ['as' => 'admin.feeScales.create']);
    $routes->post('fee-scales', 'Admin\FeeScaleController::store', ['as' => 'admin.feeScales.store']);
    $routes->get('fee-scales/(:num)/edit', 'Admin\FeeScaleController::edit/$1', ['as' => 'admin.feeScales.edit']);
    $routes->post('fee-scales/(:num)', 'Admin\FeeScaleController::update/$1', ['as' => 'admin.feeScales.update']);
    $routes->post('fee-scales/(:num)/delete', 'Admin\FeeScaleController::delete/$1', ['as' => 'admin.feeScales.delete']);

    $routes->get('gains', 'Admin\StatisticsController::gains', ['as' => 'admin.gains']);
    $routes->get('statistics', 'Admin\StatisticsController::advanced', ['as' => 'admin.statistics']);

    $routes->get('operators', 'Admin\OperatorController::index', ['as' => 'admin.operators.index']);
    $routes->get('operators/create', 'Admin\OperatorController::create', ['as' => 'admin.operators.create']);
    $routes->post('operators', 'Admin\OperatorController::store', ['as' => 'admin.operators.store']);
    $routes->get('operators/(:num)/edit', 'Admin\OperatorController::edit/$1', ['as' => 'admin.operators.edit']);
    $routes->post('operators/(:num)', 'Admin\OperatorController::update/$1', ['as' => 'admin.operators.update']);
    $routes->post('operators/(:num)/toggle', 'Admin\OperatorController::toggle/$1', ['as' => 'admin.operators.toggle']);
    $routes->post('operators/(:num)/delete', 'Admin\OperatorController::delete/$1', ['as' => 'admin.operators.delete']);

    $routes->get('operator-prefixes', 'Admin\OperatorPrefixController::index', ['as' => 'admin.operatorPrefixes.index']);
    $routes->get('operator-prefixes/create', 'Admin\OperatorPrefixController::create', ['as' => 'admin.operatorPrefixes.create']);
    $routes->post('operator-prefixes', 'Admin\OperatorPrefixController::store', ['as' => 'admin.operatorPrefixes.store']);
    $routes->get('operator-prefixes/(:num)/edit', 'Admin\OperatorPrefixController::edit/$1', ['as' => 'admin.operatorPrefixes.edit']);
    $routes->post('operator-prefixes/(:num)', 'Admin\OperatorPrefixController::update/$1', ['as' => 'admin.operatorPrefixes.update']);
    $routes->post('operator-prefixes/(:num)/toggle', 'Admin\OperatorPrefixController::toggle/$1', ['as' => 'admin.operatorPrefixes.toggle']);
    $routes->post('operator-prefixes/(:num)/delete', 'Admin\OperatorPrefixController::delete/$1', ['as' => 'admin.operatorPrefixes.delete']);

    $routes->get('commissions', 'Admin\InterOperatorCommissionController::index', ['as' => 'admin.commissions.index']);
    $routes->get('commissions/create', 'Admin\InterOperatorCommissionController::create', ['as' => 'admin.commissions.create']);
    $routes->post('commissions', 'Admin\InterOperatorCommissionController::store', ['as' => 'admin.commissions.store']);
    $routes->get('commissions/(:num)/edit', 'Admin\InterOperatorCommissionController::edit/$1', ['as' => 'admin.commissions.edit']);
    $routes->post('commissions/(:num)', 'Admin\InterOperatorCommissionController::update/$1', ['as' => 'admin.commissions.update']);
    $routes->post('commissions/(:num)/toggle', 'Admin\InterOperatorCommissionController::toggle/$1', ['as' => 'admin.commissions.toggle']);
    $routes->post('commissions/(:num)/delete', 'Admin\InterOperatorCommissionController::delete/$1', ['as' => 'admin.commissions.delete']);

    $routes->get('settlements', 'Admin\SettlementController::index', ['as' => 'admin.settlements.index']);
    $routes->get('reports', 'Admin\ReportController::index', ['as' => 'admin.reports.index']);
    $routes->get('reports/export/pdf', 'Admin\ReportController::exportPdf', ['as' => 'admin.reports.export.pdf']);
    $routes->get('reports/export/excel', 'Admin\ReportController::exportExcel', ['as' => 'admin.reports.export.excel']);

    $routes->get('customers', 'Admin\CustomerController::index', ['as' => 'admin.customers.index']);
    $routes->get('customers/(:num)', 'Admin\CustomerController::show/$1', ['as' => 'admin.customers.show']);
    $routes->post('customers/(:num)/delete', 'Admin\CustomerController::delete/$1', ['as' => 'admin.customers.delete']);
});

$routes->group('client', static function ($routes) {
    // Routes publiques (connexion automatique, sans mot de passe)
    $routes->get('login', 'Client\AuthController::login', ['as' => 'client.login']);
    $routes->post('login', 'Client\AuthController::attempt', ['as' => 'client.login.attempt']);
    $routes->get('logout', 'Client\AuthController::logout', ['as' => 'client.logout']);

    // Routes protégées (nécessitent une session client ouverte)
    $routes->group('', ['filter' => 'clientAuth'], static function ($routes) {
        $routes->get('/', 'Client\DashboardController::index', ['as' => 'client.dashboard']);

        $routes->get('solde', 'Client\BalanceController::index', ['as' => 'client.balance']);

        $routes->get('depot', 'Client\DepositController::create', ['as' => 'client.deposit.create']);
        $routes->post('depot', 'Client\DepositController::store', ['as' => 'client.deposit.store']);

        $routes->get('retrait', 'Client\WithdrawalController::create', ['as' => 'client.withdrawal.create']);
        $routes->post('retrait', 'Client\WithdrawalController::store', ['as' => 'client.withdrawal.store']);

        $routes->get('transfert', 'Client\TransferController::create', ['as' => 'client.transfer.create']);
        $routes->post('transfert', 'Client\TransferController::store', ['as' => 'client.transfer.store']);
        $routes->get('transfert/preview', 'Client\TransferController::preview', ['as' => 'client.transfer.preview']);

        $routes->get('transfert-multiple', 'Client\MultipleTransferController::create', ['as' => 'client.transfer.multiple.create']);
        $routes->post('transfert-multiple', 'Client\MultipleTransferController::store', ['as' => 'client.transfer.multiple.store']);
        $routes->get('transfert-multiple/preview', 'Client\MultipleTransferController::preview', ['as' => 'client.transfer.multiple.preview']);

        $routes->get('historique', 'Client\HistoryController::index', ['as' => 'client.history.index']);
        $routes->get('historique/(:num)', 'Client\HistoryController::show/$1', ['as' => 'client.history.show']);

        $routes->get('profil', 'Client\ProfileController::index', ['as' => 'client.profile']);
    });
});
