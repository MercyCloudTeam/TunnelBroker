<?php

use App\Http\Controllers\ASNController;
use App\Http\Controllers\BGPController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\TunnelController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::get('/nodes', [NodeController::class, 'index'])->name('node.index');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::resource('/tunnels', TunnelController::class)->except([
        'create', 'edit'
    ]);
    Route::put('/tunnels/{tunnel}/rebuild', [TunnelController::class, 'rebuild'])->name('tunnel.rebuild');

    Route::resource('/asn', ASNController::class)->except([
        'create', 'edit'
    ]);
    Route::resource('/bgp', BGPController::class)->except([
        'create', 'edit'
    ]);
    Route::put('/bgp/{bgp}/rebuild', [BGPController::class, 'rebuild'])->name('bgp.rebuild');
//    Route::resource('/asn', FRR::class)->except([
//        'create', 'edit'
//    ]);
//    Route::get('/validate/asn',[ASNController::class,'index'])->name('bgp.validate');//验证ASN
    Route::post('/validate/asn', [ASNController::class, 'store'])->name('asn.validate');//验证ASN
});
