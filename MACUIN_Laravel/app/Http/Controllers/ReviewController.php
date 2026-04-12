<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://macuin_api:8080');
    }

    public function store(Request $request)
    {
        $token = session('token');
        if (!$token) {
            return redirect()->route('login')->withErrors(['message' => 'Debes iniciar sesión para reseñar.']);
        }

        $response = Http::withToken($token)->post($this->apiUrl . '/v1/resenas/', [
            'id_producto' => $request->id_producto,
            'calificacion' => $request->calificacion,
            'comentario' => $request->comentario
        ]);

        if ($response->successful()) {
            return back()->with('success', '¡Gracias por tu reseña!');
        }

        $error = $response->json()['detail'] ?? 'Error al enviar la reseña.';
        return back()->withErrors(['message' => $error]);
    }
}
