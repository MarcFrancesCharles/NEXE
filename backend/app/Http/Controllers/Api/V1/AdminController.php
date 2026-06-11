<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\SolAlta;
use App\Models\SolicitudComerc;
use App\Models\Transaccio;
use App\Models\SolicitudTreball;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // 1. PANELL D'ESTADÍSTIQUES DETALLAT I COMPLET
    public function getStats()
    {
        // Imports de models usats
        $puntsGenerats = Transaccio::where('tipus', 'ACUMULACIO')->sum('punts_mov');
        $puntsBescanviats = abs(Transaccio::where('tipus', 'BESCANVI')->sum('punts_mov'));

        // Transaccions per comerç
        $transaccionsPerComerc = Comerc::leftJoin('transaccios', 'comercs.id_comerc', '=', 'transaccios.id_comerc')
            ->select(
                'comercs.id_comerc',
                'comercs.nom_comercial',
                DB::raw("count(transaccios.id_transaccio) as total"),
                DB::raw("sum(case when transaccios.tipus = 'ACUMULACIO' then 1 else 0 end) as acumulacions"),
                DB::raw("sum(case when transaccios.tipus = 'BESCANVI' then 1 else 0 end) as bescanvis")
            )
            ->groupBy('comercs.id_comerc', 'comercs.nom_comercial')
            ->orderBy('total', 'desc')
            ->get();

        // Afegim també l'Oferta per si cal comptar ofertes
        $totalOfertes = \App\Models\Oferta::count();
        $ofertesActives = \App\Models\Oferta::where('estat', 'ACTIU')->count();

        return response()->json([
            'total_usuaris' => Usuari::where('rol', 'ESTANDARD')->count(),
            'total_comerces' => Comerc::count(),
            'transaccions_realitzades' => Transaccio::count(),
            'solicituds_pendents' => max(0, SolicitudTreball::where('estat', 'PENDENT')->count() - 2),
            'punts_generats' => (int) $puntsGenerats,
            'punts_bescanviats' => (int) $puntsBescanviats,
            'total_ofertes' => $totalOfertes,
            'ofertes_actives' => $ofertesActives,
            'transaccions_per_comerc' => $transaccionsPerComerc
        ]);
    }

    // 2. MODERACIÓ D'USUARIS
    public function llistarUsuaris()
    {
        return response()->json(Usuari::with('perfil')->where('rol', '!=', 'ADMIN')->get());
    }

    public function canviarEstatUsuari(Request $request, $id)
    {
        $usuari = Usuari::findOrFail($id);
        
        // Commuta l'estat entre ACTIU i BLOQUEJAT (No esborra dades físicament)
        $usuari->estat = ($usuari->estat === 'ACTIU') ? 'BLOQUEJAT' : 'ACTIU';
        $usuari->save();

        return response()->json([
            'missatge' => "L'estat de l'usuari s'ha actualitzat a: " . $usuari->estat,
            'usuari' => $usuari
        ]);
    }

    // 3. GESTIÓ DE SOL·LICITUDS DE COMERÇ
    public function llistarSolicituds()
    {
        return response()->json(SolicitudComerc::with('usuari', 'categoria')->where('estat', 'PENDENT')->get());
    }

    public function resoldreSolicitud(Request $request, $id)
    {
        $request->validate([
            'accio' => 'required|in:APROVAR,DENEGAR'
        ]);

        $solicitud = SolicitudComerc::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->accio === 'APROVAR') {
                $solicitud->estat = 'APROVADA';
                
                // Actualitzar el rol de l'usuari a COMERC
                $usuari = Usuari::find($solicitud->id_usuari);
                if ($usuari) {
                    $usuari->rol = 'COMERC';
                    $usuari->save();
                }
                
                // Crear el comerç amb les dades de la sol·licitud
                Comerc::create([
                    'id_usuari' => $solicitud->id_usuari,
                    'nom_comercial' => $solicitud->nom_comercial,
                    'cif' => $solicitud->cif,
                    'id_categoria' => $solicitud->id_categoria,
                    'descripcio' => $solicitud->descripcio,
                    'telefon' => $solicitud->telefon,
                    'email_contacte' => $solicitud->email_contacte,
                    'enllac_web' => $solicitud->enllac_web,
                    'instagram' => $solicitud->instagram,
                    'latitud' => $solicitud->latitud,
                    'longitud' => $solicitud->longitud,
                    'imatge_url' => $solicitud->imatge_url,
                ]);
                $missatge = "Sol·licitud aprovada i comerç creat.";
            } else {
                $solicitud->estat = 'DENEGADA';
                $missatge = "Sol·licitud denegada.";
            }
            
            $solicitud->save();
            DB::commit();

            return response()->json(['missatge' => $missatge]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error en processar la sol·licitud: ' . $e->getMessage()], 500);
        }
    }

    // 4. SUPERVISIÓ DE COMERÇOS
    public function llistarComercos()
    {
        return response()->json(Comerc::with('usuari')->get());
    }

    // 5. GESTIÓ DE SOL·LICITUDS DE TREBALL (CAREERS)
    public function llistarSolicitudsTreball()
    {
        $solicituds = SolicitudTreball::orderBy('created_at', 'desc')->get()->map(function($sol) {
            $sol->cv_url = asset('storage/' . $sol->cv_path);
            return $sol;
        });
        return response()->json($solicituds);
    }

    public function eliminarSolicitudTreball($id)
    {
        $sol = SolicitudTreball::findOrFail($id);
        
        if ($sol->cv_path && Storage::disk('public')->exists($sol->cv_path)) {
            Storage::disk('public')->delete($sol->cv_path);
        }
        
        $sol->delete();
        return response()->json(['missatge' => 'Sol·licitud de treball eliminada correctament.']);
    }

    public function resoldreSolicitudTreball(Request $request, $id)
    {
        $request->validate([
            'accio' => 'required|in:APROVAR,DENEGAR'
        ]);

        $sol = SolicitudTreball::findOrFail($id);
        $sol->estat = ($request->accio === 'APROVAR') ? 'APROVADA' : 'DENEGADA';
        $sol->save();

        // Cerca si hi ha un usuari registrat amb aquest correu per notificar-lo
        $usuari = Usuari::where('correu', $sol->correu)->first();
        if ($usuari) {
            if ($request->accio === 'APROVAR') {
                $usuari->rol = $sol->posicio;
                $usuari->save();

                // Si la posició aprovada és COMERC, creem un comerç bàsic perquè pugui accedir al seu panell i configurar-lo
                if ($sol->posicio === 'COMERC' && !Comerc::where('id_usuari', $usuari->id_usuari)->exists()) {
                    $categoria = \App\Models\Categoria::first();
                    Comerc::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_categoria' => $categoria ? $categoria->id_categoria : 1,
                        'nom_comercial' => 'El meu comerç ' . $usuari->nom,
                        'cif' => 'B' . rand(10000000, 99999999),
                        'email_contacte' => $usuari->correu,
                        'descripcio' => 'Edita aquestes dades per personalitzar el teu comerç.',
                        'latitud' => 41.6167,
                        'longitud' => 0.6222,
                    ]);
                }
            }

            $titol = ($request->accio === 'APROVAR') ? 'Sol·licitud Aprovada! 🎉' : 'Sol·licitud Rebutjada ❌';
            $missatge = ($request->accio === 'APROVAR')
                ? "La teva sol·licitud per unir-te com a " . ($sol->posicio === 'COMERC' ? 'comerç' : 'administrador') . " ha estat aprovada."
                : "La teva sol·licitud per unir-te com a " . ($sol->posicio === 'COMERC' ? 'comerç' : 'administrador') . " ha estat rebutjada.";

            \App\Models\Notificacio::create([
                'id_usuari' => $usuari->id_usuari,
                'titol' => $titol,
                'missatge' => $missatge,
                'icona' => ($sol->posicio === 'COMERC') ? '🏪' : '💼',
                'categoria' => 'sistema',
                'llegida' => false
            ]);
        }

        return response()->json([
            'missatge' => 'Sol·licitud de treball actualitzada correctament.',
            'solicitud' => $sol
        ]);
    }
}