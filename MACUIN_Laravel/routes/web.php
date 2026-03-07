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
    $productos = config('productos');
    $categorias = [];
    foreach ($productos as $id => $prod) {
        $categorias[$prod['categoria']][$id] = $prod;
    }
    return view('catalogo', ['categorias' => $categorias]);
})->name('catalogo');

Route::get('/carrito', function () {
    return view('carrito');
})->name('carrito');

Route::get('/detalle-producto/{id}', function ($id) {
    $productos = config('productos');

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
