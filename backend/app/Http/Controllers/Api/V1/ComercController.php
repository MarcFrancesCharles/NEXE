<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comerc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ComercController extends Controller
{
    // Funció pública per llistar tots els comerços
    public function index()
    {
        // Retornem els comerços i fem un "with" per incloure el nom de la categoria associada
        $comercos = Comerc::with('categoria')->get();
        return response()->json($comercos);
    }

    // Funció per llistar totes les categories
    public function categories()
    {
        return response()->json(\App\Models\Categoria::all());
    }

    // Retorna el comerç de l'usuari autenticat
    public function elMeuComerc(Request $request)
    {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::with('categoria')->where('id_usuari', $userId)->first();
        
        // Si és ADMIN i no té un comerc propi, agafem el primer de la llista per defecte
        // per permetre-li veure i gestionar la secció "La meva botiga"
        if (!$comerc && $request->user()->rol === 'ADMIN') {
            $comerc = Comerc::with('categoria')->first();
        }

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat'], 404);
        }
        return response()->json($comerc);
    }

    // Actualitza les dades del comerç de l'usuari autenticat
    public function actualitzarComerc(Request $request)
    {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::where('id_usuari', $userId)->first();

        // Si és ADMIN i no té comerc propi, li deixem actualitzar el primer de la llista
        if (!$comerc && $request->user()->rol === 'ADMIN') {
            $comerc = Comerc::first();
        }

        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç associat'], 404);
        }

        try {
            $request->validate([
                'nom_comercial' => 'sometimes|required|string|max:100',
                'id_categoria' => 'sometimes|required|exists:categorias,id_categoria',
                'cif' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('comercs', 'cif')->ignore($comerc->id_comerc, 'id_comerc')],
                'imatge' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validació', 'errors' => $e->errors()], 422);
        }

        if ($request->has('nom_comercial') && $request->nom_comercial !== 'null' && $request->nom_comercial !== '') {
            $comerc->nom_comercial = $request->nom_comercial;
        }
        if ($request->has('id_categoria') && $request->id_categoria !== 'null' && $request->id_categoria !== '') {
            $comerc->id_categoria = $request->id_categoria;
        }
        if ($request->has('cif') && $request->cif !== 'null' && $request->cif !== '') {
            $comerc->cif = $request->cif;
        }

        if ($request->hasFile('imatge')) {
            // Esborrem la imatge vella si existeix
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