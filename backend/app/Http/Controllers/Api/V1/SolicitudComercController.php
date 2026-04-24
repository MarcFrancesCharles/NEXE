<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SolicitudComerc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudComercController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'nom_comercial' => 'required|string|max:100',
            'descripcio' => 'nullable|string|max:255',
            'telefon' => 'nullable|integer',
            'email_contacte' => 'nullable|email|max:100',
            'enllac_web' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:100',
            'cif' => 'required|string|max:20',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'imatge_url' => 'nullable|string', // Pot ser URL o es sobreescriurà si hi ha fitxer
            'imatge_file' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'missatge' => 'Error de validació',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuari = $request->user();

        if ($usuari->rol === 'COMERC' || $usuari->rol === 'ADMIN') {
            return response()->json(['missatge' => 'Ja ets un comerç o administrador.'], 403);
        }

        $solicitudExistent = SolicitudComerc::where('id_usuari', $usuari->id_usuari)
            ->where('estat', 'PENDENT')
            ->first();

        if ($solicitudExistent) {
            return response()->json(['missatge' => 'Ja tens una sol·licitud pendent.'], 400);
        }

        $dades = $validator->validated();
        
        // Gestionar la imatge (Arxiu té prioritat sobre URL si s'envien ambdós)
        if ($request->hasFile('imatge_file')) {
            $path = $request->file('imatge_file')->store('solicituds', 'public');
            $dades['imatge_url'] = '/storage/' . $path;
        }

        $solicitud = SolicitudComerc::create([
            ...$dades,
            'id_usuari' => $usuari->id_usuari,
            'estat' => 'PENDENT'
        ]);

        return response()->json([
            'missatge' => 'Sol·licitud enviada correctament',
            'solicitud' => $solicitud
        ], 201);
    }
}
