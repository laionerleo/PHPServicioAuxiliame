<?php

namespace App\Http\Controllers;

use App\Models\UsuarioChatIA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
            return response()->json([
                'error' => true,
                'mensaje' => 'Error de validación',
                'datos' => $validator->errors()
            ], 200);
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
            'error' => false,
            'mensaje' => 'Historial de chat obtenido correctamente',
            'datos' => ['mensajes' => $mensajes]
        ], 200);
    }

    public function enviar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error de validación',
                'datos' => $validator->errors()
            ], 200);
        }

        $user = Auth::user();
        $this->saveChatMessage($user->Usuario, $request->mensaje, 'usuario');

        $classification = $this->getClassificationFromAI($request->mensaje);

        if (strtolower(trim($classification)) !== 'sí') {
            $responseMessage = 'Este asistente solo responde preguntas relacionadas con mecánica de vehículos.';
            $this->saveChatMessage($user->Usuario, $responseMessage, 'ia');
            return response()->json([
                'error' => false,
                'mensaje' => 'Respuesta generada',
                'datos' => ['respuesta' => $responseMessage]
            ], 200);
        }

        $aiResponse = $this->getAiResponse($request->mensaje);
        $this->saveChatMessage($user->Usuario, $aiResponse, 'ia');

        return response()->json([
            'error' => false,
            'mensaje' => 'Respuesta generada por la IA',
            'datos' => ['respuesta' => $aiResponse]
        ], 200);
    }

    private function saveChatMessage($usuario, $mensaje, $origen)
    {
        $nextSerial = (UsuarioChatIA::where('Usuario', $usuario)->max('Serial') ?? 0) + 1;

        UsuarioChatIA::create([
            'Usuario' => $usuario,
            'Serial' => $nextSerial,
            'Mensaje' => $mensaje,
            'Origen' => $origen,
            'Fecha' => now()->toDateString(),
            'Hora' => now()->toTimeString(),
        ]);
    }

    private function getClassificationFromAI($message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.secret'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => '¿Este mensaje está relacionado con mecánica o fallas técnicas de vehículos? Responde solo con: sí o no'],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $response->json('choices.0.message.content');
    }

    private function getAiResponse($message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.secret'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant specialized in car mechanics.'],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $response->json('choices.0.message.content');
    }
}
