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

        $routes->get('historique', 'Client\HistoryController::index', ['as' => 'client.history.index']);
        $routes->get('historique/(:num)', 'Client\HistoryController::show/$1', ['as' => 'client.history.show']);

        $routes->get('profil', 'Client\ProfileController::index', ['as' => 'client.profile']);
    });
});
