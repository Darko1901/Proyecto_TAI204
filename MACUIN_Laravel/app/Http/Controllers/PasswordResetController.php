<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('app.api_url', 'http://macuin_api:8080');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        try {
            // 1. Verificar si el correo existe en la API
            $response = Http::post($this->apiUrl . '/v1/auth/recuperar', [
                'email' => $email
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $nombre = $data['nombre'];

                // 2. Enviar Correo con Gmail (en serio)
                Mail::to($email)->send(new PasswordResetMail($nombre, $email));

                return back()->with('success', '¡Enviado! Revisa tu bandeja de entrada de Gmail.');
            } else {
                return back()->withErrors(['email' => 'No encontramos ninguna cuenta asociada a este correo.']);
            }

        } catch (\Exception $e) {
            // Log the error
            \Log::error("Error en recuperación: " . $e->getMessage());
            
            // Si el correo falló por configuración, al menos informamos al dev
            return back()->withErrors(['email' => 'Error al intentar enviar el correo. Verifica las credenciales SMTP en el archivo .env']);
        }
    }
}
