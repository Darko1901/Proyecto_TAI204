<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!session()->has('token')) return redirect()->route('login');
    return view('index');
})->name('home');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registro'])->name('registro.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/recuperar', function () {
    return view('recuperar');
})->name('recuperar');
Route::post('/recuperar', [App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])->name('recuperar.post');

Route::get('/dashboard', function () {
    if (!session()->has('token')) return redirect()->route('login');
    return view('dashboard');
})->name('dashboard');

// Catalogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/detalle-producto/{id}', [CatalogoController::class, 'show'])->name('detalle_producto');

// Carrito y Checkout
Route::get('/carrito', function () {
    return view('carrito');
})->name('carrito');

Route::get('/checkout', function () {
    if (!session()->has('token')) return redirect()->route('login');
    return view('checkout');
})->name('checkout');

Route::post('/pedido/crear', [OrderController::class, 'checkout'])->name('pedido.crear');

// Pedidos y Perfil
Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');
Route::post('/perfil/actualizar', [ProfileController::class, 'update'])->name('perfil.update');

Route::get('/pedidos', [OrderController::class, 'index'])->name('pedidos');
Route::get('/pedido/{id}', [OrderController::class, 'show'])->name('pedido.detalle');
Route::get('/pedido/{id}/factura', [OrderController::class, 'downloadInvoice'])->name('pedido.factura');
Route::post('/pedido/{id}/cancelar', [OrderController::class, 'cancel'])->name('pedido.cancelar');

// Reseñas
Route::post('/resena/crear', [ReviewController::class, 'store'])->name('resena.crear');
