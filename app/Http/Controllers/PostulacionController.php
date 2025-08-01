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
            return response()->json([
                'error' => true,
                'mensaje' => 'Error de validación',
                'datos' => $validator->errors()
            ], 200);
        }

        $user = Auth::user();

        if ($user->Estado != 1) {
            return response()->json([
                'error' => true,
                'mensaje' => 'El usuario mecánico no está activo',
                'datos' => null
            ], 200);
        }

        $pedido = Pedido::find($request->pedido);

        if (!$pedido || $pedido->Estado != 1) {
            return response()->json([
                'error' => true,
                'mensaje' => 'El pedido no existe o no está pendiente',
                'datos' => null
            ], 200);
        }

        $existingPostulacion = PedidoPostulacion::where('Pedido', $request->pedido)
            ->where('Usuario', $user->Usuario)
            ->first();

        if ($existingPostulacion) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Ya se ha postulado a este pedido',
                'datos' => null
            ], 200);
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
            'error' => false,
            'mensaje' => 'Postulación registrada correctamente',
            'datos' => [
                'postulacion' => [
                    'Pedido' => $postulacion->Pedido,
                    'Serial' => $postulacion->Serial,
                    'Usuario' => $postulacion->Usuario,
                    'TiempoEstimado' => $postulacion->TiempoEstimado,
                    'Precio' => $postulacion->Precio,
                    'Estado' => $postulacion->Estado,
                ]
            ]
        ], 200);
    }

    public function aceptarPostulacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido' => 'required|integer',
            'serial' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error de validación',
                'datos' => $validator->errors()
            ], 200);
        }

        $user = Auth::user();

        $pedido = Pedido::where('Pedido', $request->pedido)
            ->where('Usuario', $user->Usuario)
            ->first();

        if (!$pedido) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Pedido no encontrado o no le pertenece',
                'datos' => null
            ], 200);
        }

        if ($pedido->Estado != 1) {
            return response()->json([
                'error' => true,
                'mensaje' => 'El pedido no está pendiente',
                'datos' => null
            ], 200);
        }

        $postulacion = PedidoPostulacion::where('Pedido', $request->pedido)
            ->where('Serial', $request->serial)
            ->first();

        if (!$postulacion) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Postulación no encontrada',
                'datos' => null
            ], 200);
        }

        DB::transaction(function () use ($request, $user, $pedido, $postulacion) {
            // Update other postulaciones to 'rechazado'
            PedidoPostulacion::where('Pedido', $request->pedido)
                ->where('Serial', '!=', $request->serial)
                ->update(['Estado' => 'rechazado']);

            // Update the accepted postulacion
            $postulacion->Estado = 'aceptado';
            $postulacion->Usr = $user->Usuario;
            $postulacion->UsrFecha = now()->toDateString();
            $postulacion->UsrHora = now()->toTimeString();
            $postulacion->save();

            // Update the pedido
            $pedido->Estado = 2; // en_proceso
            $pedido->Usr = $user->Usuario;
            $pedido->UsrFecha = now()->toDateString();
            $pedido->UsrHora = now()->toTimeString();
            $pedido->save();
        });

        return response()->json([
            'error' => false,
            'mensaje' => 'Postulación aceptada correctamente',
            'datos' => [
                'postulacion' => [
                    'Pedido' => $postulacion->Pedido,
                    'Serial' => $postulacion->Serial,
                    'Usuario' => $postulacion->Usuario,
                    'Estado' => $postulacion->Estado,
                ]
            ]
        ], 200);
    }
}
