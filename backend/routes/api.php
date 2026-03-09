<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComercController;
use App\Http\Controllers\Api\V1\OfertaController;
use App\Http\Controllers\Api\V1\TransaccioController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Middleware\CheckRole;

// --- RUTES PÚBLIQUES (Sense Token) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/comerces', [ComercController::class, 'index']); // Llistar comerços
Route::get('/ofertes', [OfertaController::class, 'index']);  // Llistar ofertes actives


// --- RUTES PROTEGIDES (Amb Token de Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Rutes Generals per a qualsevol usuari logat
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil-meu', function (Request $request) {
        return response()->json($request->user()->load(['perfil', 'transaccions']));
    });

    // Rutes Exclusives: ESTÀNDARD
    Route::middleware([CheckRole::class.':ESTANDARD'])->group(function () {
        Route::post('/tiquets/escanejar', [TransaccioController::class, 'escanejarTiquet']);
        Route::post('/ofertes/bescanviar', [TransaccioController::class, 'bescanviarOferta']);
    });

    // Rutes Exclusives: COMERÇ
    Route::middleware([CheckRole::class.':COMERÇ'])->group(function () {
        Route::post('/ofertes', [OfertaController::class, 'crearOferta']);
        Route::get('/comerces/vendes', [TransaccioController::class, 'vendesComerc']);
    });

    // Rutes Exclusives: ADMINISTRADOR
    Route::middleware([CheckRole::class.':ADMIN'])->group(function () {
        Route::get('/admin/usuaris', [AdminController::class, 'llistarUsuaris']);
        Route::put('/admin/usuaris/{id}/estat', [AdminController::class, 'canviarEstat']);
    });

});