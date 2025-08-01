<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\ServiciosController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('usuario', [UsuarioController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::post('pedido', [ServiciosController::class, 'crearPedido']);
    Route::post('pedido/listar', [ServiciosController::class, 'listarPedidos']);
    Route::post('pedido/ver', [ServiciosController::class, 'verPedido']);
    Route::post('pedido/detalle-con-postulaciones', [ServiciosController::class, 'detalleConPostulaciones']);
    Route::post('pedido/finalizar', [ServiciosController::class, 'finalizarPedido']);
    Route::post('pedido/postulacion/crear', [PostulacionController::class, 'crearPostulacion']);
    Route::post('pedido/postulacion/aceptar', [PostulacionController::class, 'aceptarPostulacion']);
    Route::post('chat/historial', [ChatController::class, 'historial']);
});
