<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\SolAlta;
use App\Models\SolicitudComerc;
use App\Models\Transaccio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'solicituds_pendents' => SolicitudComerc::where('estat', 'PENDENT')->count(),
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
}