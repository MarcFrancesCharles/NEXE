<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SolicitudComerc;
use Illuminate\Http\Request;

class SolicitudComercController extends Controller
{
    // Obtener la solicitud del usuario autenticado
    public function getMiaSolicitud(Request $request)
    {
        $userId = $request->user()->id_usuari;
        $solicitud = SolicitudComerc::where('id_usuari', $userId)->first();

        if (!$solicitud) {
            return response()->json(['missatge' => 'No tens cap sol·licitud de comerç.'], 404);
        }

        return response()->json($solicitud);
    }

    public function store(Request $request)
    {
        try {
            // 1. Validació bàsica (més relaxada per evitar bloquejos invisibles)
            $request->validate([
                'nom_comercial' => 'required|string',
                'cif' => 'required|string',
                'id_categoria' => 'required'
            ]);

            // 2. Evitem sol·licituds duplicades
            $existent = SolicitudComerc::where('id_usuari', $request->user()->id_usuari)
                               ->where('estat', 'PENDENT')
                               ->first();
                               
            if ($existent) {
                return response()->json(['error' => 'Ja tens una sol·licitud pendent de revisió.'], 400);
            }

            // 3. Guardem les dades
            $solicitud = SolicitudComerc::create([
                'id_usuari' => $request->user()->id_usuari,
                'nom_comercial' => $request->input('nom_comercial'),
                'cif' => $request->input('cif'),
                'id_categoria' => $request->input('id_categoria'),
                'descripcio' => $request->input('descripcio'),
                'telefon' => $request->input('telefon'),
                'email_contacte' => $request->input('email_contacte'),
                'enllac_web' => $request->input('enllac_web'),
                'instagram' => $request->input('instagram'),
                'latitud' => $request->input('latitud'),
                'longitud' => $request->input('longitud'),
                'imatge_url' => $request->input('imatge_url'),
                'estat' => 'PENDENT',
            ]);

            return response()->json([
                'missatge' => 'Sol·licitud enviada correctament! Esperant aprovació.',
                'solicitud' => $solicitud
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si falta un camp, el backend ens avisarà
            return response()->json(['error' => 'Dades incompletes: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            // Si hi ha un error de base de dades, també el veurem
            return response()->json(['error' => 'Error intern: ' . $e->getMessage()], 500);
        }
    }
}