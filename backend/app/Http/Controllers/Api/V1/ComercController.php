<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comerc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateComercRequest;

class ComercController extends Controller
{
    // Funció pública per llistar tots els comerços
    public function index()
        {
        // Retornem els comerços i fem un "with" per incloure el nom de la categoria associada
        $comercos = Comerc::with('categoria')->get();
        return response()->json($comercos);
     }


    // Retorna el comerç de l'usuari autenticat
    public function elMeuComerc(Request $request)
        {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::with('categoria')->where('id_usuari', $userId)->first();
        
        // Eliminat l'assignació de Comerc::first() per a l'ADMIN. 
        // L'admin no té "Meu Comerç". Si hi intenta entrar des del menú (cosa que el frontend hauria d'amagar), li donem error.

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat al teu compte.'], 404);
        }
        
        return response()->json($comerc);
     }

    // Actualitza les dades del comerç de l'usuari autenticat
    public function actualitzarComerc(UpdateComercRequest $request)
        {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::where('id_usuari', $userId)->first();

        if (!$comerc && $request->user()->rol === 'ADMIN') {
            $comerc = Comerc::first();
        }

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat'], 404);
        }

        // Netegem els strings "null" de l'Angular
        foreach ($request->all() as $key => $value) {
            if ($value === 'null') {
                $request->merge([$key => null]);
            }
        }

        // Ja no hi ha $request->validate() aquí!

        if ($request->filled('nom_comercial')) {
            $comerc->nom_comercial = $request->nom_comercial;
        }
        if ($request->filled('id_categoria')) {
            $comerc->id_categoria = $request->id_categoria;
        }
        if ($request->filled('cif')) {
            $comerc->cif = $request->cif;
        }

        if ($request->hasFile('imatge')) {
            if ($comerc->imatge_url) {
                Storage::disk('public')->delete($comerc->imatge_url);
            }
            $path = $request->file('imatge')->store('comerces', 'public');
            $comerc->imatge_url = $path;
        }

        $comerc->save();

        return response()->json([
            'missatge' => 'Comerç actualitzat correctament',
            'comerc' => $comerc->load('categoria')
        ]);
     }
}