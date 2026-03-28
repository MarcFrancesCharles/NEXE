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

    /**
     * Retorna un Token Xifrat permanent amb l'ID de l'usuari (El seu "Carnet Digital")
     */
    public function generarQrCarnet(Request $request)
    {
        $usuari = $request->user();
        
        // Xifrem un JSON amb l'ID de l'usuari i la paraula clau 'CARNET' per evitar que s'usi per ofertes
        $dades = json_encode([
            'tipus' => 'CARNET',
            'id_usuari' => $usuari->id_usuari
        ]);

        $tokenQr = Crypt::encryptString($dades);

        return response()->json(['qr_token' => $tokenQr], 200);
    }

    /**
     * Retorna un Token Xifrat TEMPORAL per demanar una oferta al botiguer
     */
    public function generarQrOferta(Request $request)
    {
        $request->validate(['id_oferta' => 'required|exists:ofertas,id_oferta']);
        
        $usuari = $request->user();
        
        // Xifrem l'ID de l'usuari, l'oferta, i posem una data de caducitat (15 minuts)
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

    /**
     * El botiguer escaneja el carnet del client i li suma els punts per la compra
     */
    public function atorgarPunts(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'import_compra' => 'required|numeric|min:0.1'
        ]);

        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'Error: No ets un comerç actiu.'], 403);

        try {
            // 1. Desxifrem el QR
            $dadesQr = json_decode(Crypt::decryptString($request->qr_token));

            // 2. Comprovem que sigui un QR de tipus CARNET
            if ($dadesQr->tipus !== 'CARNET') {
                return response()->json(['missatge' => 'Error: Aquest QR no és un carnet vàlid.'], 400);
            }

            $id_client = $dadesQr->id_usuari;
            $puntsGuanyats = floor($request->import_compra);

            // 3. Executem la transacció de BD
            DB::beginTransaction();

            Transaccio::create([
                'id_usuari' => $id_client,
                'id_comerc' => $comerc->id_comerc,
                'tipus' => 'ACUMULACIO',
                'punts_mov' => $puntsGuanyats,
                'data_hora' => now(),
            ]);

            $perfil = Perfil::where('id_usuari', $id_client)->first();
            $perfil->punts_totals += $puntsGuanyats;
            $perfil->save();

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

    /**
     * El botiguer escaneja el QR de l'oferta del client per restar-li els punts i donar-li el producte
     */
    public function validarBescanvi(Request $request)
    {
        $request->validate(['qr_token' => 'required|string']);

        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'Accés denegat.'], 403);

        try {
            // 1. Desxifrem el QR
            $dadesQr = json_decode(Crypt::decryptString($request->qr_token));

            // 2. Validacions de seguretat (Tipus i Caducitat)
            if ($dadesQr->tipus !== 'OFERTA') {
                return response()->json(['missatge' => 'Error: Has escanejat un Carnet, no una Oferta.'], 400);
            }
            if (now()->timestamp > $dadesQr->caduca_el) {
                return response()->json(['missatge' => 'Error: Aquest codi QR ha caducat (Han passat més de 15 minuts).'], 400);
            }

            $id_client = $dadesQr->id_usuari;
            $oferta = Oferta::findOrFail($dadesQr->id_oferta);

            // 3. Comprovem que l'oferta pertanyi a la botiga que l'està escanejant
            if ($oferta->id_comerc !== $comerc->id_comerc) {
                return response()->json(['missatge' => 'Error: Aquesta oferta és d\'una altra botiga!'], 400);
            }

            $perfil = Perfil::where('id_usuari', $id_client)->first();

            // 4. Comprovem si el client té prou punts al moment exacte de l'escaneig
            if ($perfil->punts_totals < $oferta->cost_punts) {
                return response()->json(['missatge' => 'El client no té prou punts per aquesta oferta.'], 400);
            }

            // 5. Procés segur de Base de dades
            DB::beginTransaction();

            $perfil->punts_totals -= $oferta->cost_punts;
            $perfil->save();

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
    // 🟣 LLISTAT DE VENDES
    // ====================================================================
    public function vendesComerc(Request $request)
    {
        $comerc = Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        if (!$comerc) return response()->json(['missatge' => 'No tens cap comerç actiu.'], 404);

        $vendes = Transaccio::with(['usuari', 'oferta'])
                    ->where('id_comerc', $comerc->id_comerc)
                    ->where('tipus', 'BESCANVI')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($vendes);
    }
}