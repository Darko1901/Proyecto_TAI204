<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://macuin_api:8080');
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $response = Http::asForm()->post($this->apiUrl . '/v1/auth/token', [
            'username' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Session::put('token', $data['access_token']);
            Session::put('user_email', $request->email);
            
            return redirect()->route('home')->with('success', 'Bienvenido de nuevo.');
        }

        return back()->withErrors(['error' => 'Credenciales incorrectas o servidor no disponible.']);
    }

    public function showRegistro()
    {
        return view('registro');
    }

    public function registro(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'correo' => 'required|email|max:100',
            'password' => 'required|min:6',
        ]);

        $response = Http::post($this->apiUrl . '/v1/auth/registro', [
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'correo' => $request->correo,
            'password' => $request->password,
            'id_rol' => 5, // ID de rol para clientes externos (5 = Cliente)
            'telefono' => $request->telefono ?? '0000000000'
        ]);

        if ($response->successful()) {
            // Realizar login automático tras el registro para enviar al Index directamente
            $loginResponse = Http::asForm()->post($this->apiUrl . '/v1/auth/token', [
                'username' => $request->correo,
                'password' => $request->password,
            ]);

            if ($loginResponse->successful()) {
                $data = $loginResponse->json();
                Session::put('token', $data['access_token']);
                Session::put('user_email', $request->correo);
                return redirect()->route('home')->with('success', 'Registro exitoso. ¡Bienvenido a MACUIN!');
            }

            return redirect()->route('login')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
        }

        $error = $response->json()['detail'] ?? 'Error al registrar usuario.';
        return back()->withErrors(['error' => $error])->withInput();
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
