<?php

use App\Http\Controllers\TunnelAPIController;
use App\Http\Controllers\TunnelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function (){
    Route::apiResource('/tunnel','TunnelAPIController');
    Route::post('/ddns-tunnel/{tunnel}',[TunnelAPIController::class,'tunnelDDNSUpdate']);//动态IP用户 请求本接口即可实现对Remote IP的更新
});
