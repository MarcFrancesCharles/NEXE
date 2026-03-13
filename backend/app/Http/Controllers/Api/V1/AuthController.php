<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuari;
use App\Models\Perfil;
use App\Models\Comerc;
use App\Models\Categoria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. FUNCIÓ DE REGISTRE
    public function register(Request $request)
    {
        // 1. Definim les regles bàsiques
        $regles = [
            'nom' => 'required|string|max:100',
            'correu' => 'required|email|unique:usuaris,correu',
            'contrasenya' => 'required|min:8',
            'rol' => ['required', Rule::in(['ESTANDARD', 'COMERC', 'ADMIN'])],
        ];

        // 2. Afegim regles extra NOMÉS si és un COMERC
        if ($request->rol === 'COMERC') {
            $regles['id_categoria'] = 'required|exists:categorias,id_categoria';
            $regles['cif'] = 'required|string|max:20|unique:comercs,cif';
        }

        // 3. Executem la validació
        $request->validate($regles);

        // Creem l'usuari
        $usuari = Usuari::create([
            'nom' => $request->nom,
            'correu' => $request->correu,
            'contrasenya' => Hash::make($request->contrasenya), 
            'rol' => $request->rol,
            'estat' => 'ACTIU',
        ]);

        // Creem el perfil (obligatori 1:1)
        Perfil::create([
            'id_usuari' => $usuari->id_usuari,
            'punts_totals' => 0,
        ]);

        // Si el rol és COMERC, creem també l'entrada a la taula 'comercs'
        if ($usuari->rol === 'COMERC') {
            Comerc::create([
                'id_usuari' => $usuari->id_usuari,
                'id_categoria' => $request->id_categoria,
                'nom_comercial' => $usuari->nom, // Mateix nom que l'usuari
                'cif' => $request->cif,
                // coord_gps i imatge_url son nullables per defecte
            ]);
        }

        // Generem el Token
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

    // 4. FUNCIÓ D'ACTUALITZAR PERFIL
    public function actualitzarPerfil(Request $request)
    {
        $usuari = $request->user();

        $request->validate([
            'nom' => 'nullable|string|max:100',
            'correu' => 'required|email|unique:usuaris,correu,' . $usuari->id_usuari . ',id_usuari',
            'contrasenya' => 'nullable|min:8',
        ]);

        $usuari->nom = $request->nom;
        $usuari->correu = $request->correu;

        if ($request->filled('contrasenya')) {
            $usuari->contrasenya = Hash::make($request->contrasenya);
        }

        $usuari->save();

        return response()->json([
            'missatge' => 'Perfil actualitzat correctament',
            'usuari' => $usuari->load(['perfil', 'transaccions'])
        ]);
    }
}