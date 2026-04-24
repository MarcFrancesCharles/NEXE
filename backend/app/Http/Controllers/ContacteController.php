<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Contacte;
use Illuminate\Http\Request;

class ContacteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'assumpte' => 'required|string|max:255',
            'missatge' => 'required|string',
        ]);

        Contacte::create($validated);

        // Aquí podrías añadir Mail::to('admin@nexe.cat')->send(...)

        return response()->json(['message' => 'Missatge enviat correctament.'], 201);
    }
}