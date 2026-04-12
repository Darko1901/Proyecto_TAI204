<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CatalogoController extends Controller
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://macuin_api:8080');
    }

    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $q = $request->query('q');
        $sort = $request->query('sort');
        $categoriaId = $request->query('categoria');

        $response = Http::get($this->apiUrl . '/v1/autopartes/', [
            'page' => $page,
            'limit' => 18,
            'q' => $q,
            'sort' => $sort,
            'categoria' => $categoriaId
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $productosApi = $data['items'];
            
            // Re-mapear para que coincida con el diseño original
            $productosMapeados = array_map(function($p) {
                return [
                    'id' => $p['id_producto'],
                    'nombre' => $p['nombre_producto'],
                    'marca' => $p['marca'],
                    'compatibilidad' => $p['modelo'], 
                    'price_num' => $p['precio'],
                    'precio' => '$' . number_format($p['precio'], 2) . ' MXN',
                    'imagen' => $p['imagen'],
                    'stock' => $p['stock'] ?? 0,
                    'disponible' => ($p['stock'] ?? 0) > 0,
                    'rating' => $p['avg_rating'] ?? 0,
                    'review_count' => $p['review_count'] ?? 0,
                    'categoria' => $this->getNombreCategoria($p['id_categoria'])
                ];
            }, $productosApi);

            // Si es una petición AJAX, devolvemos JSON (para scroll infinito)
            if ($request->ajax()) {
                return response()->json([
                    'items' => $productosMapeados,
                    'total' => $data['total'],
                    'page' => $data['page'],
                    'total_pages' => $data['total_pages']
                ]);
            }

            // Agrupar por categoría
            $categorias = [];
            foreach ($productosMapeados as $prod) {
                $categorias[$prod['categoria']][] = $prod;
            }
            
            return view('catalogo', [
                'categorias' => $categorias,
                'items' => $productosMapeados,
                'total' => $data['total'],
                'currentPage' => $data['page'],
                'totalPages' => $data['total_pages'],
                'q' => $q,
                'sort' => $sort
            ]);
        }

        return view('catalogo', ['categorias' => [], 'items' => []])->with('error', 'No se pudo cargar el catálogo.');
    }


    public function show($id)
    {
        $response = Http::get($this->apiUrl . '/v1/autopartes/' . $id);
        $resenasResponse = Http::get($this->apiUrl . '/v1/resenas/producto/' . $id);

        if ($response->successful()) {
            $p = $response->json();
            $resenas = $resenasResponse->successful() ? $resenasResponse->json() : [];
            
            // Mapeo detallado para detalle_producto (ahora incluye campos faltantes)
            $producto = [
                'id_producto' => $p['id_producto'],
                'nombre_producto' => $p['nombre_producto'],
                'marca' => $p['marca'] ?? 'MACUIN',
                'modelo' => $p['modelo'] ?? 'Universal',
                'precio' => $p['precio'], // Numérico para cálculos
                'precio_formateado' => '$' . number_format($p['precio'], 2) . ' MXN',
                'imagen' => $p['imagen'],
                'stock' => $p['stock'] ?? 10,
                'descripcion' => $p['descripcion'] ?? 'Sin descripción disponible.',
                'id_categoria' => $this->getNombreCategoria($p['id_categoria'] ?? 0),
                'compatibilidad' => $p['modelo'] ?? 'Varios modelos'
            ];

            return view('detalle_producto', [
                'producto' => $producto, 
                'id' => $id,
                'resenas' => $resenas
            ]);
        }

        return redirect()->route('catalogo')->with('error', 'Producto no encontrado.');
    }

    private function getNombreCategoria($id)
    {
        $map = [
            1 => 'Frenos',
            2 => 'Suspension y Direccion',
            3 => 'Iluminacion',
            4 => 'Carroceria y Colision',
            5 => 'Llantas',
            6 => 'Kits de Afinacion',
            7 => 'Sensores y Electrico',
            8 => 'Aceites y Aditivos'
        ];
        return $map[$id] ?? 'General';
    }
}
