<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://macuin_api:8080');
    }

    public function index()
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        $response = Http::withToken($token)->get($this->apiUrl . '/v1/pedidos/mis-pedidos');

        $pedidos = $response->successful() ? $response->json() : [];

        return view('pedidos', ['pedidos' => $pedidos]);
    }

    public function show($id)
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        $pedidoResponse = Http::withToken($token)->get($this->apiUrl . '/v1/pedidos/' . $id);
        $envioResponse = Http::withToken($token)->get($this->apiUrl . '/v1/pedidos/' . $id . '/envio');

        if ($pedidoResponse->successful()) {
            return view('pedido_detalle', [
                'pedido' => $pedidoResponse->json(),
                'envio' => $envioResponse->successful() ? $envioResponse->json() : null,
                'id' => $id
            ]);
        }

        return redirect()->route('pedidos')->withErrors(['message' => 'Pedido no encontrado.']);
    }

    public function cancel($id)
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        $response = Http::withToken($token)->patch($this->apiUrl . '/v1/pedidos/' . $id . '/cancelar');

        if ($response->successful()) {
            return back()->with('success', 'Pedido cancelado correctamente.');
        }

        $error = $response->json()['detail'] ?? 'No se pudo cancelar el pedido.';
        return back()->withErrors(['message' => $error]);
    }

    public function checkout(Request $request)
    {
        $token = session('token');
        if (!$token) {
            return redirect()->route('login')->withErrors(['message' => 'Debes iniciar sesión para comprar.']);
        }

        $request->validate([
            'direccion'          => 'required|string|min:5',
            'ciudad'             => 'required|string|min:2',
            'codigo_postal'      => ['required', 'regex:/^\d{5}$/'],
            'telefono_contacto'  => ['required', 'regex:/^\d{10}$/'],
            'metodo_pago'        => 'required|in:Tarjeta,Efectivo',
        ], [
            'direccion.required'         => 'La dirección es obligatoria.',
            'direccion.min'              => 'La dirección debe tener al menos 5 caracteres.',
            'ciudad.required'            => 'La ciudad es obligatoria.',
            'ciudad.min'                 => 'La ciudad debe tener al menos 2 caracteres.',
            'codigo_postal.required'     => 'El código postal es obligatorio.',
            'codigo_postal.regex'        => 'El código postal debe tener exactamente 5 dígitos numéricos.',
            'telefono_contacto.required' => 'El teléfono de contacto es obligatorio.',
            'telefono_contacto.regex'    => 'El teléfono debe tener exactamente 10 dígitos numéricos, sin letras ni espacios.',
            'metodo_pago.required'       => 'Selecciona un método de pago.',
            'metodo_pago.in'             => 'Método de pago no válido.',
        ]);

        $carritoJson = $request->input('carrito_data', '[]');
        $itemsRaw = json_decode($carritoJson, true);
        
        $items = [];
        if (is_array($itemsRaw)) {
            foreach ($itemsRaw as $item) {
                $items[] = [
                    'id_producto' => $item['id_puro'] ?? $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precioNum'] ?? $item['precio_venta']
                ];
            }
        }
        
        if (empty($items)) {
            return back()->withErrors(['message' => 'El carrito está vacío o no se pudo procesar.']);
        }

        $metodo = $request->metodo_pago ?? 'Tarjeta';
        $pedidoData = [
            'detalles' => $items,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'telefono_contacto' => substr($request->telefono_contacto ?? '0000000000', 0, 20),
            'notas' => "[PAGO: " . strtoupper($metodo) . "] " . ($request->referencias ?? ''),
            'metodo_pago' => $metodo // Pasamos esto para que la vista de confirmación lo tenga
        ];

        $response = Http::withToken($token)->post($this->apiUrl . '/v1/pedidos/', $pedidoData);

        if ($response->successful()) {
            $pedidoResponse = $response->json();
            $pedidoResponse['metodo_pago'] = $metodo; // Aseguramos que la vista sepa el método
            return view('confirmacion_pedido', ['pedido' => $pedidoResponse]);
        }

        $error = $response->json()['detail'] ?? 'Error al procesar el pedido.';
        return back()->withInput()->withErrors(['message' => $error]);
    }

    public function downloadInvoice($id)
    {
        $token = session('token');
        if (!$token) return redirect()->route('login');

        $response = Http::withToken($token)->get($this->apiUrl . '/v1/pedidos/' . $id);
        if (!$response->successful()) return back()->withErrors(['message' => 'No se encontró el pedido.']);

        $pedido = $response->json();
        
        // Simulación de generación de factura
        $content = "FACTURA EJECUTIVA - MACUIN\n";
        $content .= "============================\n";
        $content .= "PEDIDO #" . str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) . "\n";
        $content .= "FECHA: " . $pedido['fecha_pedido'] . "\n";
        $content .= "CLIENTE: " . session('user_name', 'Cliente MACUIN') . "\n";
        $content .= "DIRECCIÓN: " . ($pedido['direccion_entrega'] ?? 'N/A') . "\n";
        $content .= "----------------------------\n";
        $content .= "PRODUCTOS:\n";
        foreach ($pedido['detalles'] as $d) {
            $subtotal = ($d['precio_unitario'] ?? 0) * ($d['cantidad'] ?? 1);
            $content .= "- " . ($d['nombre_producto'] ?? $d['producto']['nombre_producto'] ?? 'Producto') . " x" . $d['cantidad'] . " : $" . number_format($subtotal, 2) . "\n";
        }
        $content .= "----------------------------\n";
        $content .= "TOTAL: $" . number_format($pedido['total'], 2) . " MXN\n";
        $content .= "============================\n";
        $content .= "¡Gracias por su compra!\n";

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="Factura_MACUIN_'. $id .'.txt"');
    }
}
