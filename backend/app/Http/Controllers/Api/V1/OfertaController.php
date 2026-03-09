<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Oferta;
use App\Models\Comerc;

class OfertaController extends Controller
{
    // Funció per crear una nova recompensa (Només COMERÇOS)
    public function crearOferta(Request $request)
    {
        $request->validate([
            'titol' => 'required|string|max:100',
            'cost_punts' => 'required|integer|min:1',
        ]);

        // Busquem la botiga física que gestiona aquest usuari logat
        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();

        if (!$comerc) {
            return response()->json(['missatge' => 'Error: No tens cap comerç associat al teu compte.'], 404);
        }

        // Creem l'oferta a la base de dades
        $oferta = Oferta::create([
            'id_comerc' => $comerc->id_comerc,
            'titol' => $request->titol,
            'cost_punts' => $request->cost_punts,
            'estat' => 1 // 1 = Activa per defecte
        ]);

        return response()->json([
            'missatge' => 'Oferta publicada correctament!', 
            'oferta' => $oferta
        ], 201);
    }

    public function index()
    {
        // Retornem les ofertes actives (estat = 1) amb la informació del comerç
        $ofertes = Oferta::with('comerc')->where('estat', 1)->get();
        return response()->json($ofertes);
    }
}