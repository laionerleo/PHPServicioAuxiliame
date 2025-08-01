<?php

namespace App\Http\Controllers;

use App\Models\UsuarioChatIA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function historial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limite' => 'integer|min:1',
            'offset' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $limite = $request->input('limite', 10);
        $offset = $request->input('offset', 0);

        $mensajes = UsuarioChatIA::where('Usuario', $user->Usuario)
            ->orderBy('Serial', 'desc')
            ->skip($offset)
            ->take($limite)
            ->get(['Serial as serial', 'Mensaje as mensaje', 'Origen as origen', 'Fecha as fecha', 'Hora as hora']);

        return response()->json([
            'estado' => 'ok',
            'mensajes' => $mensajes,
        ]);
    }
}
