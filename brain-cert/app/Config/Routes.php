<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('/api/auth/login','API\AuthApiController::login');
$routes->post('/api/auth/logout', 'Api\AuthApiController::logout',['filter' => 'apiauth']);
$routes->group('api', ['filter' => 'apiauth'], function($routes) {
    $routes->post('product', 'Api\ProductController::create');
	$routes->get('product/(:num)', 'Api\ProductController::get_product/$1');
	$routes->get('product', 'Api\ProductController::get_product');
	$routes->put('product', 'Api\ProductController::update_product');
	$routes->delete('product', 'Api\ProductController::delete_product');
	$routes->post('category','Api\CategoryController::create_category');
	$routes->get('category','Api\CategoryController::get_category');
	$routes->get('category/(:num)','Api\CategoryController::get_category/$1');
	$routes->put('category','Api\CategoryController::update_category');
	$routes->post('currency','Api\CurrencyController::create_currency');
	$routes->post('unit','Api\UnitController::create_unit');
	$routes->post('quantity-type','Api\QuantityTypeController::create_quantity_type');
	$routes->post('product/inventory','Api\InventoryController::inventory');
	$routes->get('product/inventory','Api\InventoryController::get_inventory');
});

$routes->get('/api/products', 'Home::product_list');
$routes->get('/', 'Home::index');

service('auth')->routes($routes);