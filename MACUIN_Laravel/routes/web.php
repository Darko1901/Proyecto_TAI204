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
    return view('detalle_producto');
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
