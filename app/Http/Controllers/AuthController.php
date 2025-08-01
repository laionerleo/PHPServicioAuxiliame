<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('telefono', 'contrasena');

        $request->validate([
            'telefono' => 'required',
            'contrasena' => 'required',
        ]);

        $user = User::where('Telefono', $credentials['telefono'])->first();

        if (!$user || !Hash::check($credentials['contrasena'], $user->contrasena)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json([
            'token' => $token,
            'usuario' => [
                'Usuario' => $user->Usuario,
                'Nombre' => $user->Nombre,
                'Telefono' => $user->Telefono,
                'Rol' => $user->Rol,
            ]
        ]);
    }
}
