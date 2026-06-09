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
        $comercos = Comerc::with('categoria')->orderBy('nom_comercial', 'asc')->get();
        return response()->json($comercos);
    }

    // Retorna el comerç de l'usuari autenticat
    public function elMeuComerc(Request $request)
    {
        $userId = $request->user()->id_usuari;
        $comerc = Comerc::with('categoria')->where('id_usuari', $userId)->first();

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

        // Netegem els strings "null" que pot enviar Angular
        foreach ($request->all() as $key => $value) {
            if ($value === 'null') {
                $request->merge([$key => null]);
            }
        }

        // --- Camps bàsics ---
        if ($request->filled('nom_comercial')) {
            $comerc->nom_comercial = $request->nom_comercial;
        }
        if ($request->filled('id_categoria')) {
            $comerc->id_categoria = $request->id_categoria;
        }
        if ($request->filled('cif')) {
            $comerc->cif = $request->cif;
        }

        // --- Nous camps de contacte i descripció ---
        // Usem "has" perquè l'usuari pot voler esborrar un camp enviant-ne el valor buit
        if ($request->has('descripcio')) {
            $comerc->descripcio = $request->descripcio;
        }
        if ($request->has('telefon')) {
            $comerc->telefon = $request->telefon;
        }
        if ($request->has('email_contacte')) {
            $comerc->email_contacte = $request->email_contacte;
        }
        if ($request->has('enllac_web')) {
            $comerc->enllac_web = $request->enllac_web;
        }
        if ($request->has('instagram')) {
            // Netegem la "@" si l'usuari l'ha posat al davant
            $comerc->instagram = ltrim($request->instagram, '@');
        }

        // --- Imatge de portada ---
        if ($request->hasFile('imatge')) {
            // Esborrem la imatge anterior si existia
            if ($comerc->imatge_url) {
                Storage::disk('public')->delete($comerc->imatge_url);
            }
            $path = $request->file('imatge')->store('comerces', 'public');
            $comerc->imatge_url = $path;
        }

        $comerc->save();

        return response()->json([
            'missatge' => 'Comerç actualitzat correctament',
            'comerc'   => $comerc->load('categoria')
        ]);
    }

    // Retorna la informació pública d'un comerç i les seves ofertes ACTIVES
    public function show(Request $request, $id)
    {
        $comerc = Comerc::with(['categoria', 'ofertes' => function ($query) {
            $query->where('estat', 1) // Només ofertes actives
                  ->where(function ($q) {
                      $q->whereNull('data_fi')->orWhere('data_fi', '>=', now());
                  })
                  ->orderBy('cost_punts', 'asc');
        }])->findOrFail($id);

        $seguidorsCount = $comerc->seguidors()->count();
        $seguint = false;
        $usuari = $request->user('sanctum');
        if ($usuari && $usuari->rol === 'ESTANDARD') {
            $seguint = $usuari->comercosSeguint()->where('seguidors.id_comerc', $comerc->id_comerc)->exists();
        }

        $comercArray = $comerc->toArray();
        $comercArray['seguidors_count'] = $seguidorsCount;
        $comercArray['seguint'] = $seguint;

        return response()->json($comercArray);
    }

    // Seguir un comerç
    public function seguirComerc(Request $request, $id)
    {
        $usuari = $request->user();
        if ($usuari->rol !== 'ESTANDARD') {
            return response()->json(['missatge' => 'Només els clients poden seguir comerços.'], 403);
        }

        $comerc = Comerc::findOrFail($id);
        $usuari->comercosSeguint()->syncWithoutDetaching([$comerc->id_comerc]);

        \App\Models\Notificacio::create([
            'id_usuari' => $usuari->id_usuari,
            'id_comerc' => $comerc->id_comerc,
            'titol' => 'Nou comerç seguit! 🏪',
            'missatge' => "Ara estàs seguint a '" . $comerc->nom_comercial . "'. Rebràs notificacions quan publiquin noves ofertes!",
            'icona' => '🏪',
            'categoria' => 'general',
            'llegida' => false
        ]);

        return response()->json([
            'missatge' => 'Ara estàs seguint aquest comerç.',
            'seguidors_count' => $comerc->seguidors()->count(),
            'seguint' => true
        ]);
    }

    // Deixar de seguir un comerç
    public function deixarSeguirComerc(Request $request, $id)
    {
        $usuari = $request->user();
        if ($usuari->rol !== 'ESTANDARD') {
            return response()->json(['missatge' => 'Només els clients poden deixar de seguir comerços.'], 403);
        }

        $comerc = Comerc::findOrFail($id);
        $usuari->comercosSeguint()->detach($comerc->id_comerc);

        \App\Models\Notificacio::create([
            'id_usuari' => $usuari->id_usuari,
            'id_comerc' => $comerc->id_comerc,
            'titol' => 'Has deixat de seguir un comerç',
            'missatge' => "Has deixat de seguir a '" . $comerc->nom_comercial . "'.",
            'icona' => '📭',
            'categoria' => 'general',
            'llegida' => false
        ]);

        return response()->json([
            'missatge' => 'Has deixat de seguir aquest comerç.',
            'seguidors_count' => $comerc->seguidors()->count(),
            'seguint' => false
        ]);
    }
}