<?php

use Illuminate\Support\Facades\Route;

// Rutas de autenticación para clientes externos
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function () {
    // Lógica de autenticación pendiente
    return redirect()->route('dashboard');
})->name('login.post');

Route::get('/registro', function () {
    return view('registro');
})->name('registro');

Route::post('/registro', function () {
    // Lógica de registro pendiente
    return redirect()->route('login');
})->name('registro.post');

// Rutas de recuperación de contraseña
Route::get('/recuperar', function () {
    return view('recuperar');
})->name('recuperar');

Route::post('/recuperar', function () {
    // Lógica de envío de correo pendiente
    return back()->with('success', 'Enlace de recuperación enviado al correo.');
})->name('recuperar.post');

// Rutas del dashboard y funcionalidades principales
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/catalogo', function () {
    return view('catalogo');
})->name('catalogo');

Route::get('/carrito', function () {
    return view('carrito');
})->name('carrito');

Route::get('/detalle-producto/{id}', function ($id) {
    $productos = [
        1 => [
            'nombre' => 'Amortiguador Delantero',
            'imagen' => 'amortiguador.png',
            'precio' => '$1,250.00 MXN',
            'descripcion' => 'Amortiguador de alta resistencia para suspensión delantera. Diseñado para soportar las condiciones más exigentes de los caminos mexicanos.',
            'marca' => 'Monroe',
            'modelo' => 'AM-4521',
            'compatibilidad' => 'Nissan Tsuru, Chevrolet Aveo, Volkswagen Jetta A4',
            'garantia' => '2 años o 40,000 km',
            'disponible' => true,
        ],
        2 => [
            'nombre' => 'Kit de Frenos',
            'imagen' => 'frenos.png',
            'precio' => '$890.00 MXN',
            'descripcion' => 'Kit completo de pastillas y discos de freno de alto rendimiento. Incluye 4 pastillas y 2 discos ventilados para máxima seguridad.',
            'marca' => 'Brembo',
            'modelo' => 'BK-7832',
            'compatibilidad' => 'Ford Fiesta, Chevrolet Spark, Nissan March',
            'garantia' => '1 año o 20,000 km',
            'disponible' => true,
        ],
    ];

    $producto = $productos[$id] ?? null;

    if (!$producto) {
        return redirect()->route('catalogo')->with('error', 'Producto no encontrado');
    }

    return view('detalle_producto', ['producto' => $producto, 'id' => $id]);
})->name('detalle_producto');

Route::get('/perfil', function () {
    return view('perfil');
})->name('perfil');

Route::post('/perfil/actualizar', function () {
    // Lógica de actualización de perfil pendiente
    return back()->with('success', 'Perfil actualizado correctamente');
})->name('perfil.update');

Route::post('/perfil/cambiar-password', function () {
    // Lógica de cambio de contraseña pendiente
    return back()->with('success', 'Contraseña actualizada correctamente');
})->name('perfil.cambiar-password');

// Rutas de Pedidos
Route::get('/pedidos', function () {
    return view('pedidos');
})->name('pedidos');

Route::get('/pedido/{id}', function ($id) {
    return view('pedido_detalle');
})->name('pedido.detalle');

Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::post('/pedido/crear', function () {
    // Lógica de creación de pedido pendiente
    return redirect()->route('pedidos')->with('success', 'Pedido creado exitosamente');
})->name('pedido.crear');

Route::post('/pedido/{id}/cancelar', function ($id) {
    // Lógica de cancelación de pedido pendiente
    return redirect()->route('pedido.detalle', $id)->with('success', 'Pedido cancelado');
})->name('pedido.cancelar');
