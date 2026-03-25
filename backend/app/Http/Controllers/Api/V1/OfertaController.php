<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Oferta;
use App\Models\Comerc;
use App\Http\Requests\StoreOfertaRequest;

class OfertaController extends Controller
{
    // Funció per crear una nova recompensa (Només COMERÇOS)
    public function crearOferta(StoreOfertaRequest $request)
    {
        // Hem eliminat tot el bloc de $request->validate() !!!

        // Busquem la botiga física que gestiona aquest usuari logat
        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();

        if (!$comerc) {
            return response()->json(['missatge' => 'Error: No tens cap comerç associat al teu compte.'], 404);
        }

        // Creem l'oferta a la base de dades amb les noves columnes
        $oferta = Oferta::create([
            'id_comerc' => $comerc->id_comerc,
            'titol' => $request->titol,
            'descripcio' => $request->descripcio,
            'cost_punts' => $request->cost_punts,
            'data_fi' => $request->data_fi,       
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
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::where('id_usuari', $userId)->first();

        // Hem eliminat l'assignació de Comerc::first() a l'ADMIN!

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat.'], 403);
        }

        $ofertes = Oferta::where('id_comerc', $comerc->id_comerc)
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json($ofertes, 200);
    }

    // Funció per eliminar una oferta pròpia
    public function eliminarOferta(Request $request, $id)
    {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::where('id_usuari', $userId)->first();

        // Si és ADMIN, busquem l'oferta directament sense filtrar per comerc propi
        if ($request->user()->rol === 'ADMIN') {
            $oferta = Oferta::find($id);
        } else {
            if (!$comerc) {
                return response()->json(['missatge' => 'No tens cap comerç associat.'], 403);
            }
            // Busquem l'oferta assegurant-nos que pertany a aquest comerç
            $oferta = Oferta::where('id_oferta', $id)
                            ->where('id_comerc', $comerc->id_comerc)
                            ->first();
        }

        if (!$oferta) {
            return response()->json(['missatge' => 'Oferta no trobada o no tens permís.'], 404);
        }

        $oferta->delete();

        return response()->json(['missatge' => 'Oferta eliminada correctament.'], 200);
    }

    // Funció per modificar una oferta existent
    public function modificarOferta(Request $request, $id)
    {
        $user = $request->user();
        
        // Si és Admin, pot editar l'oferta que vulgui
        if ($user->rol === 'ADMIN') {
            $oferta = Oferta::findOrFail($id);
        } else {
            // Si és Comerç, només pot editar les SEVES ofertes
            $comerc = Comerc::where('id_usuari', $user->id_usuari)->first();
            if (!$comerc) return response()->json(['missatge' => 'No autoritzat'], 403);
            
            $oferta = Oferta::where('id_oferta', $id)->where('id_comerc', $comerc->id_comerc)->first();
            if (!$oferta) return response()->json(['missatge' => 'Oferta no trobada o no et pertany'], 404);
        }

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

        if ($request->hasFile('imatge')) {
            $ruta = $request->file('imatge')->store('ofertas', 'public');
            $dadesActualitzar['imatge'] = $ruta;
        }

        $oferta->update($dadesActualitzar);

        return response()->json(['missatge' => 'Oferta actualitzada!', 'oferta' => $oferta], 200);
    }
}