<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComercController;
use App\Http\Controllers\Api\V1\OfertaController;
use App\Http\Controllers\Api\V1\TransaccioController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\CategoriaController;
use App\Http\Controllers\Api\V1\SolicitudComercController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\Api\V1\ContacteController;

// --- RUTES PÚBLIQUES (Sense Token) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/comerces', [ComercController::class, 'index']); // Llistar comerços
Route::get('/ofertes', [OfertaController::class, 'index']);  // Llistar ofertes actives
Route::get('/categories', [CategoriaController::class, 'index']); // Llistar categories amb subcategories
Route::get('/comerces/{id}', [ComercController::class, 'show']);
Route::post('/contacte', [ContacteController::class, 'store']);



// --- RUTES PROTEGIDES (Amb Token de Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Rutes Generals per a qualsevol usuari logat
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil-meu', function (Request $request) {
        return response()->json(
            $request->user()->load([
                'perfil', 
                'transaccions' => function ($query) {
                    // Ordenamos de más reciente a más antigua
                    $query->orderBy('data_hora', 'desc')
                          // Cargamos las relaciones para tener los nombres e imágenes
                          ->with(['comerc', 'oferta']); 
                }
            ])
        );
    });
    Route::put('/perfil-meu', [AuthController::class, 'actualitzarPerfil']);
    Route::post('/solicituds-comerc', [SolicitudComercController::class, 'store']);
    Route::get('/solicituds-comerc/mia', [SolicitudComercController::class, 'getMiaSolicitud']);

    // Rutes Exclusives: ESTÀNDARD (El Client)
    Route::middleware([CheckRole::class.':ESTANDARD'])->group(function () {
        // Generació de QRs xifrats per mostrar a la botiga
        Route::get('/client/carnet-qr', [TransaccioController::class, 'generarQrCarnet']);
        Route::post('/client/oferta-qr', [TransaccioController::class, 'generarQrOferta']);
    });

    // Rutes Exclusives: COMERC (El Botiguer)
    Route::middleware([CheckRole::class.':COMERC'])->group(function () {
        // Escaneig i validació
        Route::post('/comerc/atorgar-punts', [TransaccioController::class, 'atorgarPunts']);
        Route::post('/comerc/validar-oferta', [TransaccioController::class, 'validarBescanvi']);
        
        Route::post('/ofertes', [OfertaController::class, 'crearOferta']);
        Route::get('/comerc/vendes', [TransaccioController::class, 'vendesComerc']);
        Route::get('/les-meves-ofertes', [OfertaController::class, 'lesMevesOfertes']);
        
        // GESTIÓ DEL PROPI COMERÇ
        Route::get('/el-meu-comerc', [ComercController::class, 'elMeuComerc']);
        Route::post('/el-meu-comerc', [ComercController::class, 'actualitzarComerc']); 
    });

    // Rutes Exclusives: ADMINISTRADOR
    Route::middleware([CheckRole::class.':ADMIN'])->group(function () {
        // Estadístiques
        Route::get('/admin/stats', [AdminController::class, 'getStats']);
        
        // Usuaris
        Route::get('/admin/usuaris', [AdminController::class, 'llistarUsuaris']);
        Route::put('/admin/usuaris/{id}/estat', [AdminController::class, 'canviarEstatUsuari']);
        
        // Sol·licituds
        Route::get('/admin/solicituds', [AdminController::class, 'llistarSolicituds']);
        Route::post('/admin/solicituds/{id}/resoldre', [AdminController::class, 'resoldreSolicitud']);
        
        // Comerços
        Route::get('/admin/comerces', [AdminController::class, 'llistarComercos']);
    });

    // Rutes Exclusives: COMERÇ i ADMIN
    Route::middleware([CheckRole::class.':COMERC,ADMIN'])->group(function () {
        // Per modificar i eliminar ofertes
        Route::put('/ofertes/{id}', [OfertaController::class, 'modificarOferta']);
        Route::delete('/ofertes/{id}', [OfertaController::class, 'eliminarOferta']);
    });

});