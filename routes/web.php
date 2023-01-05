<?php

use App\Http\Controllers\ASNController;
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

Route::get('/', function () {
    $nodes = \App\Models\Node::all();
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'nodes'=> $nodes
    ]);
});

Route::get('/nodes',[NodeController::class,'index'])->name('node.index');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('/tunnels', TunnelController::class)->except([
        'create', 'edit'
    ]);

    Route::get('/validate/asn',[ASNController::class,'index'])->name('bgp.validate');//验证ASN
    Route::post('/validate/asn',[ASNController::class,'store']);
});

