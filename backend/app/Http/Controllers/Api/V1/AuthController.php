<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuari;
use App\Models\Perfil;
use App\Models\Comerc;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        // Ja no hi ha $request->validate() aquí! Tot està al RegisterUserRequest.

        try {
            DB::beginTransaction();

            $usuari = Usuari::create([
                'nom' => $request->nom,
                'correu' => $request->correu,
                'contrasenya' => Hash::make($request->contrasenya), 
                'rol' => $request->rol,
                'estat' => 'ACTIU',
            ]);

            Perfil::create([
                'id_usuari' => $usuari->id_usuari,
                'punts_totals' => 0,
            ]);

            if ($usuari->rol === 'COMERC') {
                Comerc::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_categoria' => $request->id_categoria,
                    'nom_comercial' => $usuari->nom,
                    'cif' => $request->cif,
                    'direccio' => $request->direccio,
                    'latitud' => $request->latitud,    
                    'longitud' => $request->longitud,  
                ]);
            }

            DB::commit();

            $token = $usuari->createToken('auth_token')->plainTextToken;

            return response()->json([
                'missatge' => 'Usuari registrat correctament',
                'usuari' => $usuari,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'missatge' => 'S\'ha produït un error al registrar l\'usuari',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate(['correu' => 'required|email', 'contrasenya' => 'required']);
        $usuari = Usuari::where('correu', $request->correu)->first();

        if (!$usuari || !Hash::check($request->contrasenya, $usuari->contrasenya)) {
            throw ValidationException::withMessages(['correu' => ['Les credencials són incorrectes.']]);
        }

        if ($usuari->estat === 'BLOQUEJAT') {
            return response()->json(['missatge' => 'Aquest compte està bloquejat.'], 403);
        }

        $token = $usuari->createToken('auth_token')->plainTextToken;
        return response()->json(['missatge' => 'Sessió iniciada', 'usuari' => $usuari, 'rol' => $usuari->rol, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['missatge' => 'Sessió tancada correctament']);
    }

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
        return response()->json(['missatge' => 'Perfil actualitzat', 'usuari' => $usuari->load(['perfil', 'transaccions'])]);
    }
}