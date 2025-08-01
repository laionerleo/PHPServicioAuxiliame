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

    public function listarPedidos(Request $request)
    {
        $user = Auth::user();

        $pedidos = DB::table('PEDIDO as p')
            ->join('TIPOPEDIDO as tp', 'p.TipoPedido', '=', 'tp.TipoPedido')
            ->leftJoin('POSTULANTE as post', 'p.Pedido', '=', 'post.Pedido')
            ->select(
                'p.Pedido',
                'tp.Nombre as TipoPedido',
                'p.FechaRegistro',
                DB::raw("CASE p.Estado WHEN 1 THEN 'pendiente' ELSE 'otro' END as Estado"),
                'p.Latitud',
                'p.Longitud',
                DB::raw('COUNT(post.Pedido) as CantidadPostulantes')
            )
            ->where('p.Usuario', $user->Usuario)
            ->groupBy('p.Pedido', 'tp.Nombre', 'p.FechaRegistro', 'p.Estado', 'p.Latitud', 'p.Longitud')
            ->get();

        return response()->json(['pedidos' => $pedidos]);
    }

    public function verPedido(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        $pedido = DB::table('PEDIDO as p')
            ->join('TIPOPEDIDO as tp', 'p.TipoPedido', '=', 'tp.TipoPedido')
            ->select(
                'p.Pedido',
                'tp.Nombre as TipoPedido',
                'p.FechaRegistro',
                'p.Latitud',
                'p.Longitud',
                'p.Detalles',
                DB::raw("CASE p.Estado WHEN 1 THEN 'pendiente' WHEN 2 THEN 'en proceso' WHEN 3 THEN 'finalizado' ELSE 'otro' END as Estado")
            )
            ->where('p.Usuario', $user->Usuario)
            ->where('p.Pedido', $request->pedido)
            ->first();

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json(['pedido' => $pedido]);
    }
}
