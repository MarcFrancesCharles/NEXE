<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Models\Transaccio;
use App\Models\Perfil;
use App\Models\Comerc;
use App\Models\Oferta;

class TransaccioController extends Controller
{
    // ====================================================================
    // 🟢 FUNCIONS PEL CLIENT (ESTÀNDARD) - GENERACIÓ DE QRS
    // ====================================================================

    public function generarQrCarnet(Request $request)
    {
        $usuari = $request->user();
        
        $dades = json_encode([
            'tipus' => 'CARNET',
            'id_usuari' => $usuari->id_usuari
        ]);

        $tokenQr = Crypt::encryptString($dades);

        return response()->json(['qr_token' => $tokenQr], 200);
    }

    public function generarQrOferta(Request $request)
    {
        $request->validate(['id_oferta' => 'required|exists:ofertas,id_oferta']);
        
        $usuari = $request->user();
        
        $dades = json_encode([
            'tipus' => 'OFERTA',
            'id_usuari' => $usuari->id_usuari,
            'id_oferta' => $request->id_oferta,
            'caduca_el' => now()->addMinutes(15)->timestamp
        ]);

        $tokenQr = Crypt::encryptString($dades);

        return response()->json([
            'missatge' => 'QR d\'oferta generat. Mostra\'l al botiguer abans de 15 minuts.',
            'qr_token' => $tokenQr,
            'caduca_el' => now()->addMinutes(15)->toDateTimeString()
        ], 200);
    }

    // ====================================================================
    // 🔵 FUNCIONS PEL BOTIGUER (COMERC) - ESCANEIG I VALIDACIÓ
    // ====================================================================

    public function atorgarPunts(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'import_compra' => 'required|numeric|min:0.1'
        ]);

        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'Error: No ets un comerç actiu.'], 403);

        try {
            $dadesQr = json_decode(Crypt::decryptString($request->qr_token));

            if ($dadesQr->tipus !== 'CARNET') {
                return response()->json(['missatge' => 'Error: Aquest QR no és un carnet vàlid.'], 400);
            }

            $id_client = $dadesQr->id_usuari;
            $puntsGuanyats = floor($request->import_compra);

            DB::beginTransaction();

            // ⚠️ SOLUCIÓ PRO: Increment Atòmic. MySQL s'encarrega de sumar de forma segura.
            $filesAfectades = Perfil::where('id_usuari', $id_client)->increment('punts_totals', $puntsGuanyats);

            // Evitem l'error 500 (Problema 5). Si retorna 0, és que aquest perfil no existeix.
            if ($filesAfectades === 0) {
                DB::rollBack();
                return response()->json(['missatge' => 'Error: Aquest client no té un perfil vàlid al sistema.'], 404);
            }

            Transaccio::create([
                'id_usuari' => $id_client,
                'id_comerc' => $comerc->id_comerc,
                'tipus' => 'ACUMULACIO',
                'punts_mov' => $puntsGuanyats,
                'data_hora' => now(),
            ]);

            DB::commit();

            return response()->json([
                'missatge' => "Punts atorgats! S'han sumat $puntsGuanyats punts al client.",
                'punts_donats' => $puntsGuanyats
            ], 200);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['missatge' => 'Error: Codi QR invàlid o manipulat.'], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['missatge' => 'Error intern del servidor.'], 500);
        }
    }

    public function validarBescanvi(Request $request)
    {
        $request->validate(['qr_token' => 'required|string']);

        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'Accés denegat.'], 403);

        try {
            $dadesQr = json_decode(Crypt::decryptString($request->qr_token));

            if ($dadesQr->tipus !== 'OFERTA') {
                return response()->json(['missatge' => 'Error: Has escanejat un Carnet, no una Oferta.'], 400);
            }
            if (now()->timestamp > $dadesQr->caduca_el) {
                return response()->json(['missatge' => 'Error: Aquest codi QR ha caducat (Han passat més de 15 minuts).'], 400);
            }

            $id_client = $dadesQr->id_usuari;
            $oferta = Oferta::findOrFail($dadesQr->id_oferta);

            if ($oferta->id_comerc !== $comerc->id_comerc) {
                return response()->json(['missatge' => 'Error: Aquesta oferta és d\'una altra botiga!'], 400);
            }

            DB::beginTransaction();

            // ⚠️ SOLUCIÓ PRO: Decrement Atòmic Condicionat.
            // Li diem a MySQL: "Resta X punts NOMÉS si els punts_totals són més grans o iguals a X".
            $filesAfectades = Perfil::where('id_usuari', $id_client)
                                    ->where('punts_totals', '>=', $oferta->cost_punts)
                                    ->decrement('punts_totals', $oferta->cost_punts);

            // Si retorna 0, o el client no existeix, O intentava fer frau perquè ja no li queden prous punts!
            if ($filesAfectades === 0) {
                DB::rollBack();
                return response()->json(['missatge' => 'El client no té prou punts o l\'operació no és vàlida.'], 400);
            }

            Transaccio::create([
                'id_usuari' => $id_client,
                'id_comerc' => $comerc->id_comerc,
                'id_oferta' => $oferta->id_oferta,
                'tipus' => 'BESCANVI',
                'punts_mov' => $oferta->cost_punts,
                'data_hora' => now(),
            ]);

            DB::commit();

            return response()->json([
                'missatge' => "Oferta Validada Correctament! Pots lliurar el producte/descompte.",
                'oferta' => $oferta->titol
            ], 200);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['missatge' => 'Error: Codi QR invàlid o manipulat.'], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['missatge' => 'Error intern del servidor.'], 500);
        }
    }

    // ====================================================================
    // 🟣 LLISTAT DE VENDES I ESTADÍSTIQUES
    // ====================================================================
    public function vendesComerc(Request $request)
    {
        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'No tens cap comerç actiu.'], 404);

        // Agafem TOTES les transaccions de la botiga
        $transaccions = Transaccio::with(['usuari', 'oferta'])
                    ->where('id_comerc', $comerc->id_comerc)
                    ->orderBy('data_hora', 'desc')
                    ->get();

        // Calculem les mètriques reals
        $puntsDonats = $transaccions->where('tipus', 'ACUMULACIO')->sum('punts_mov');
        $ofertesVenudes = $transaccions->where('tipus', 'BESCANVI')->count();
        $puntsBescanviats = $transaccions->where('tipus', 'BESCANVI')->sum('punts_mov');

        return response()->json([
            'punts_donats' => $puntsDonats,
            'ofertes_venudes' => $ofertesVenudes,
            'punts_bescanviats' => $puntsBescanviats,
            'historial_vendes' => $transaccions->where('tipus', 'BESCANVI')->values()
        ]);
    }
}