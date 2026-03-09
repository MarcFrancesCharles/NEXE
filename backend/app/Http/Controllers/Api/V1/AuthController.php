<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuari;
use App\Models\Perfil;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. FUNCIÓ DE REGISTRE
    public function register(Request $request)
    {
        // Validem que les dades que ens envia l'Angular (o Postman) siguin correctes
        $request->validate([
            'correu' => 'required|email|unique:usuaris,correu',
            'contrasenya' => 'required|min:8',
        ]);

        // Creem l'usuari a la Base de Dades. Segons el teu disseny, per defecte és ESTANDARD i ACTIU.
        $usuari = Usuari::create([
            'correu' => $request->correu,
            'contrasenya' => Hash::make($request->contrasenya), // L'encriptem obligatòriament
            'rol' => 'ESTANDARD',
            'estat' => 'ACTIU',
        ]);

        // Creem automàticament el seu perfil amb 0 punts (complint la relació 1:1)
        Perfil::create([
            'id_usuari' => $usuari->id_usuari,
            'punts_totals' => 0,
        ]);

        // Generem el Token de Sanctum (el vostre JWT)
        $token = $usuari->createToken('auth_token')->plainTextToken;

        return response()->json([
            'missatge' => 'Usuari registrat correctament',
            'usuari' => $usuari,
            'token' => $token
        ], 201);
    }

    // 2. FUNCIÓ DE LOGIN
    public function login(Request $request)
    {
        // Validem que ens enviïn correu i contrasenya
        $request->validate([
            'correu' => 'required|email',
            'contrasenya' => 'required',
        ]);

        // Busquem l'usuari a la BD
        $usuari = Usuari::where('correu', $request->correu)->first();

        // Si no existeix o la contrasenya falla, donem error
        if (!$usuari || !Hash::check($request->contrasenya, $usuari->contrasenya)) {
            throw ValidationException::withMessages([
                'correu' => ['Les credencials són incorrectes.'],
            ]);
        }

        // Si l'administrador l'ha bloquejat, no el deixem entrar
        if ($usuari->estat === 'BLOQUEJAT') {
            return response()->json(['missatge' => 'Aquest compte està bloquejat.'], 403);
        }

        // Generem el Token d'inici de sessió
        $token = $usuari->createToken('auth_token')->plainTextToken;

        return response()->json([
            'missatge' => 'Sessió iniciada correctament',
            'usuari' => $usuari,
            'rol' => $usuari->rol,
            'token' => $token
        ]);
    }

    // 3. FUNCIÓ DE LOGOUT (Tancar sessió)
    public function logout(Request $request)
    {
        // Esborrem el token actual perquè ja no sigui vàlid
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['missatge' => 'Sessió tancada correctament']);
    }
}