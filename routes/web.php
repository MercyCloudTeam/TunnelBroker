<?php

use App\Http\Controllers\ASNController;
use App\Http\Controllers\NIC\RIPEController;
use App\Http\Controllers\TunnelController;
use App\Jobs\ChangeTunnelIP;
use App\Jobs\CreateIPAllocation;
use App\Models\ASN;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});


Route::middleware(['auth:sanctum', 'verified'])->group(function (){
    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('/tunnels',TunnelController::class)->except([
        'create', 'edit'
    ]);
    Route::get('/validate/asn',[ASNController::class,'index'])->name('bgp.validate');//验证ASN
    Route::post('/validate/asn',[ASNController::class,'store']);
});

