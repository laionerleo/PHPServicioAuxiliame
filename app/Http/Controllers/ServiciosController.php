<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiciosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function crearPedido(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'TipoPedido' => 'required|integer',
            'Latitud' => 'required|numeric',
            'Longitud' => 'required|numeric',
            'Detalles' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tipoPedido = DB::table('TIPOPEDIDO')
            ->where('TipoPedido', $request->TipoPedido)
            ->where('Activo', 1)
            ->first();

        if (!$tipoPedido) {
            return response()->json(['error' => 'TipoPedido no es válido o no está activo'], 400);
        }

        $user = Auth::user();

        $pedido = Pedido::create([
            'Usuario' => $user->Usuario,
            'TipoPedido' => $request->TipoPedido,
            'Latitud' => $request->Latitud,
            'Longitud' => $request->Longitud,
            'Detalles' => $request->Detalles,
            'Estado' => 1,
            'FechaRegistro' => now(),
            'Usr' => $user->Usuario,
            'UsrFecha' => now()->toDateString(),
            'UsrHora' => now()->toTimeString(),
        ]);

        return response()->json([
            'mensaje' => 'Pedido registrado correctamente',
            'pedido' => [
                'Pedido' => $pedido->Pedido,
                'Usuario' => $pedido->Usuario,
                'TipoPedido' => $pedido->TipoPedido,
                'Latitud' => $pedido->Latitud,
                'Longitud' => $pedido->Longitud,
                'Estado' => $pedido->Estado,
                'FechaRegistro' => $pedido->FechaRegistro,
            ]
        ], 201);
    }
}
