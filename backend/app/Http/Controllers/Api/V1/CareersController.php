<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudTreball;
use Illuminate\Support\Facades\Storage;

class CareersController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'correu' => 'required|email|max:100',
            'missatge' => 'required|string|min:10',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120', // Max 5MB
        ]);

        // Guardem l'arxiu a la carpeta pública 'cvs'
        $cvPath = $request->file('cv')->store('cvs', 'public');

        // Creem el registre a la base de dades
        SolicitudTreball::create([
            'nom' => $request->nom,
            'correu' => $request->correu,
            'posicio' => $request->posicio ?? 'ADMIN',
            'missatge' => $request->missatge,
            'cv_path' => $cvPath,
        ]);

        return response()->json([
            'missatge' => 'La teva sol·licitud ha estat enviada i desada correctament.',
        ], 200);
    }
}
