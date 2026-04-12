<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://macuin_api:8080');
    }

    public function edit()
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        $response = Http::withToken($token)->get($this->apiUrl . '/v1/usuarios/me');

        if ($response->successful()) {
            return view('perfil', ['usuario' => $response->json()]);
        }

        return redirect()->route('dashboard')->withErrors(['message' => 'No se pudo cargar el perfil.']);
    }

    public function update(Request $request)
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        // Primero obtenemos el ID del usuario actual
        $meResponse = Http::withToken($token)->get($this->apiUrl . '/v1/usuarios/me');
        if (!$meResponse->successful()) {
            return back()->withErrors(['message' => 'Error al identificar usuario.']);
        }

        $userId = $meResponse->json()['id_usuario'];

        $datos = $request->only(['nombre', 'apellido_paterno', 'apellido_materno', 'telefono']);
        
        if ($request->filled('password')) {
            $datos['password'] = $request->password;
        }

        $response = Http::withToken($token)->patch($this->apiUrl . '/v1/usuarios/' . $userId, $datos);

        if ($response->successful()) {
            return back()->with('success', 'Perfil actualizado correctamente.');
        }

        $error = $response->json()['detail'] ?? 'Error al actualizar perfil.';
        return back()->withErrors(['message' => $error]);
    }
}
