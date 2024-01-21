<?php

use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TunnelAPIController;
use App\Http\Controllers\TunnelController;
use App\Http\Controllers\UserPlanController;
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


Route::prefix('/result')->group(function (){
    Route::post('/tunnel/{tunnel}',[TunnelAPIController::class,'tunnelResult']);
//    Route::get('/tunnel/{tunnel}',[TunnelAPIController::class,'tunnelResult']);//获取隧道创建结果
});


Route::prefix('/telegram/bot')->group(function (){
    Route::get('/test',[TelegramBotController::class,'test']);
});


Route::prefix('/manager')->middleware(['manager.auth'])->group(function () {
    // Tunnel routes
    Route::group(['prefix' => 'tunnel'], function () {
        Route::post('create', [TunnelController::class, 'create']);
        Route::delete('delete/{tunnel}', [TunnelController::class, 'apiDelete']);
    });
    // User plan routes
    Route::prefix('/plan')->group(function () {
        Route::put('edit', [UserPlanController::class, 'edit']);
        Route::get('get', [UserPlanController::class, 'get']);
        Route::delete('delete', [UserPlanController::class, 'delete']);
    });
});

