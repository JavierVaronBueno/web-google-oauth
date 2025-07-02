<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Ruta para iniciar el proceso de autenticación de Google
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');

// Ruta de callback a la que Google redirigirá después de la autenticación
Route::get('google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Opcional: Una ruta para probar el envío de correo si ya tienes el token de acceso guardado
Route::get('send-email', [GoogleAuthController::class, 'sendEmailWithPHPMailer']);
