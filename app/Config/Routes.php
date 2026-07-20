<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
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
});
