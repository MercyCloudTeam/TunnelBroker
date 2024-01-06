<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Isifnet\PieAdmin\Admin;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/tunnels', 'TunnelController');
    $router->resource('/ip/pool', 'IPPoolController');
    $router->resource('/nodes', 'NodeController');
    $router->resource('/node-connect', 'NodeConnectController');
    $router->resource('/asn', 'ASNController');
    $router->resource('/ip/allocation', 'IPAllocationController');
    $router->resource('/user', 'UserController');
    $router->resource('/plan', 'PlanController');
//    $router->resource('/bgp/filter', 'UserController');
});
