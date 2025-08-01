<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiciosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('pedido', [ServiciosController::class, 'crearPedido']);
});
