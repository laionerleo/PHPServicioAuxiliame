<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoPostulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostulacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function crearPostulacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido' => 'required|integer',
            'tiempo_estimado' => 'required|string|max:255',
            'precio' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        if ($user->Estado != 1) {
            return response()->json(['error' => 'El usuario mecánico no está activo'], 403);
        }

        $pedido = Pedido::find($request->pedido);

        if (!$pedido || $pedido->Estado != 1) {
            return response()->json(['error' => 'El pedido no existe o no está pendiente'], 404);
        }

        $existingPostulacion = PedidoPostulacion::where('Pedido', $request->pedido)
            ->where('Usuario', $user->Usuario)
            ->first();

        if ($existingPostulacion) {
            return response()->json(['error' => 'Ya se ha postulado a este pedido'], 409);
        }

        $nextSerial = (PedidoPostulacion::where('Pedido', $request->pedido)->max('Serial') ?? 0) + 1;

        $postulacion = PedidoPostulacion::create([
            'Pedido' => $request->pedido,
            'Serial' => $nextSerial,
            'Usuario' => $user->Usuario,
            'NombreMecanico' => $user->Nombre,
            'Telefono' => $user->Telefono,
            'TiempoEstimado' => $request->tiempo_estimado,
            'Precio' => $request->precio,
            'Estado' => 'pendiente',
            'FechaRegistro' => now(),
            'Usr' => $user->Usuario,
            'UsrFecha' => now()->toDateString(),
            'UsrHora' => now()->toTimeString(),
        ]);

        return response()->json([
            'mensaje' => 'Postulación registrada correctamente',
            'postulacion' => [
                'Pedido' => $postulacion->Pedido,
                'Serial' => $postulacion->Serial,
                'Usuario' => $postulacion->Usuario,
                'TiempoEstimado' => $postulacion->TiempoEstimado,
                'Precio' => $postulacion->Precio,
                'Estado' => $postulacion->Estado,
            ]
        ], 201);
    }
}
