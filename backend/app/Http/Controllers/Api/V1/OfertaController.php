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
        // 1. Hem afegit la descripció i la data_fi a la validació
        $request->validate([
            'titol' => 'required|string|max:100',
            'cost_punts' => 'required|integer|min:1',
            'descripcio' => 'nullable|string', 
            'data_fi' => 'nullable|date',      
        ]);

        // Busquem la botiga física que gestiona aquest usuari logat
        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();

        if (!$comerc) {
            return response()->json(['missatge' => 'Error: No tens cap comerç associat al teu compte.'], 404);
        }

        // Creem l'oferta a la base de dades amb les noves columnes
        $oferta = Oferta::create([
            'id_comerc' => $comerc->id_comerc,
            'titol' => $request->titol,
            'descripcio' => $request->descripcio, // NOU
            'cost_punts' => $request->cost_punts,
            'data_fi' => $request->data_fi,       // NOU
            'estat' => 1 // 1 = Activa per defecte
        ]);

        return response()->json([
            'missatge' => 'Oferta publicada correctament!', 
            'oferta' => $oferta
        ], 201);
    }

    public function index()
    {
        // Retornem les ofertes actives (estat = 1) i que NO HAN CADUCAT
        $ofertes = Oferta::with('comerc')
            ->where('estat', 1)
            ->where(function($query) {
                // Si la data_fi és Nul·la (per sempre) O bé és més gran o igual que avui
                $query->whereNull('data_fi')
                      ->orWhere('data_fi', '>=', now());
            })
            ->get();
            
        return response()->json($ofertes);
    }

    // Funció per veure només les ofertes del comerç logat (Panell d'administració)
    public function lesMevesOfertes(Request $request)
    {
        // 1. Busquem l'ID de l'usuari (amb el truc que sabem que funciona)
        $userId = $request->user()->id_usuari ?? $request->user()->id;
        
        // 2. Trobem quin comerç és el seu
        $comerc = Comerc::where('id_usuari', $userId)->first();

        // Si per algun motiu no té comerç, tallem aquí
        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat.'], 404);
        }

        // 3. Busquem TOTES les ofertes d'aquest comerç, ordenades per data de creació
        $ofertes = Oferta::where('id_comerc', $comerc->id_comerc)
                         ->orderBy('created_at', 'desc')
                         ->get();

        // 4. Les retornem empaquetades per a l'Angular
        return response()->json($ofertes, 200);
    }

    // Funció per eliminar una oferta pròpia
    public function eliminarOferta(Request $request, $id)
    {
        $userId = $request->user()->id_usuari ?? $request->user()->id;
        $comerc = Comerc::where('id_usuari', $userId)->first();

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat.'], 403);
        }

        // Busquem l'oferta assegurant-nos que pertany a aquest comerç
        $oferta = Oferta::where('id_oferta', $id)
                        ->where('id_comerc', $comerc->id_comerc)
                        ->first();

        if (!$oferta) {
            return response()->json(['missatge' => 'Oferta no trobada o no tens permís.'], 404);
        }

        $oferta->delete();

        return response()->json(['missatge' => 'Oferta eliminada correctament.'], 200);
    }

    // Funció per modificar una oferta existent
    public function modificarOferta(Request $request, $id)
        {
            $userId = $request->user()->id_usuari ?? $request->user()->id;
            $comerc = Comerc::where('id_usuari', $userId)->first();

            if (!$comerc) return response()->json(['missatge' => 'No autoritzat'], 403);

            $oferta = Oferta::where('id_oferta', $id)->where('id_comerc', $comerc->id_comerc)->first();
            if (!$oferta) return response()->json(['missatge' => 'Oferta no trobada'], 404);

            $request->validate([
                'titol' => 'required|string|max:100',
                'cost_punts' => 'required|integer|min:1',
                'descripcio' => 'nullable|string',
                'data_fi' => 'nullable|date',
                'imatge' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' 
            ]);

            $dadesActualitzar = [
                'titol' => $request->titol,
                'descripcio' => $request->descripcio,
                'cost_punts' => $request->cost_punts,
                'data_fi' => $request->data_fi,
            ];

            // SI VE UNA IMATGE: Es crea la carpeta 'ofertas' i es guarda la foto
            if ($request->hasFile('imatge')) {
                $ruta = $request->file('imatge')->store('ofertas', 'public');
                $dadesActualitzar['imatge'] = $ruta;
            }

            $oferta->update($dadesActualitzar);

            return response()->json(['missatge' => 'Oferta actualitzada!', 'oferta' => $oferta], 200);
        }
}