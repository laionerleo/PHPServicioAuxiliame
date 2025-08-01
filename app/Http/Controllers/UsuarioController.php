<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:255|unique:USUARIO,Telefono',
            'contrasena' => 'required|string|min:6',
            'rol' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error de validación',
                'datos' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'Nombre' => $request->nombre,
            'Telefono' => $request->telefono,
            'contrasena' => Hash::make($request->contrasena),
            'Rol' => $request->rol,
            'FechaRegistro' => now(),
        ]);

        return response()->json([
            'error' => false,
            'mensaje' => 'Usuario registrado correctamente',
            'datos' => [
                'usuario' => [
                    'Usuario' => $user->Usuario,
                    'Nombre' => $user->Nombre,
                    'Telefono' => $user->Telefono,
                    'Rol' => $user->Rol,
                ]
            ]
        ], 201);
    }
}
