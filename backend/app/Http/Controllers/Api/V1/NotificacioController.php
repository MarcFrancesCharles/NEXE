<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacio;

class NotificacioController extends Controller
{
    // Llistar les notificacions de l'usuari actual
    public function index(Request $request)
    {
        \App\Http\Controllers\Api\V1\OfertaController::checkScheduledPublications();

        $usuari = $request->user();
        $notificacions = Notificacio::where('id_usuari', $usuari->id_usuari)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        return response()->json($notificacions);
    }

    // Marcar una notificació com a llegida
    public function marcarComALlegida(Request $request, $id)
    {
        $usuari = $request->user();
        $notificacio = Notificacio::where('id_usuari', $usuari->id_usuari)
                                  ->findOrFail($id);

        $notificacio->update(['llegida' => true]);

        return response()->json([
            'missatge' => 'Notificació marcada com a llegida.',
            'notificacio' => $notificacio
        ]);
    }

    // Marcar totes com a llegides
    public function marcarTotesLlegides(Request $request)
    {
        $usuari = $request->user();
        Notificacio::where('id_usuari', $usuari->id_usuari)
                   ->where('llegida', false)
                   ->update(['llegida' => true]);

        return response()->json([
            'missatge' => 'Totes les notificacions s\'han marcat com a llegides.'
        ]);
    }

    // Eliminar una notificació
    public function eliminarNotificacio(Request $request, $id)
    {
        $usuari = $request->user();
        $notificacio = Notificacio::where('id_usuari', $usuari->id_usuari)
                                  ->findOrFail($id);

        $notificacio->delete();

        return response()->json([
            'missatge' => 'Notificació eliminada correctament.'
        ]);
    }
}
