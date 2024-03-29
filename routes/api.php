<?php

use App\Http\Controllers\TelegramBotController;
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


Route::prefix('/result')->group(function (){
    Route::post('/tunnel/{tunnel}',[TunnelAPIController::class,'tunnelResult']);
//    Route::get('/tunnel/{tunnel}',[TunnelAPIController::class,'tunnelResult']);//获取隧道创建结果
});


Route::prefix('/telegram/bot')->group(function (){
    Route::get('/test',[TelegramBotController::class,'test']);
});
