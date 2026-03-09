<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiquetValidat;
use App\Models\Transaccio;
use App\Models\Perfil;
use Illuminate\Support\Facades\DB;

class TransaccioController extends Controller
{
    // Funció per a l'Usuari Estàndard: Escanejar un tiquet i guanyar punts
    public function escanejarTiquet(Request $request)
    {
        // 1. Validem les dades que rebem de l'Angular (simulant el contingut del QR)
        $request->validate([
            'codi_qr' => 'required|string',
            'id_comerc' => 'required|exists:comercs,id_comerc',
            'import_compra' => 'required|numeric|min:0.1',
            'data_emissio' => 'required|date',
        ]);

        $usuari = $request->user(); // L'usuari que fa la petició (gràcies al Token)

        // 2. Control antifrau: Comprovem que aquest QR no existeixi ja a la base de dades [cite: 201, 204]
        $tiquetExistent = TiquetValidat::where('codi_qr', $request->codi_qr)->first();
        if ($tiquetExistent) {
            return response()->json(['missatge' => 'Aquest tiquet ja ha estat validat anteriorment. Mals intent!'], 400);
        }

        // 3. Obrim una Transacció de BD: O es guarda tot, o no es guarda res
        try {
            DB::beginTransaction();

            // 4. Registrem el tiquet a TIQUET_VALIDAT 
            $tiquet = TiquetValidat::create([
                'codi_qr' => $request->codi_qr,
                'import_compra' => $request->import_compra,
                'data_emissio' => $request->data_emissio,
            ]);

            // 5. Calculem els punts. (Exemple senzill: 1 punt per cada euro gastat. Ho arrodonim a la baixa)
            $puntsGuanyats = floor($request->import_compra);

            // 6. Registrem el moviment immutable a TRANSACCIO [cite: 223, 232]
            $transaccio = Transaccio::create([
                'id_usuari' => $usuari->id_usuari, // Client [cite: 219]
                'id_comerc' => $request->id_comerc, // Botiga [cite: 220]
                'id_tiquet' => $tiquet->id_tiquet, // Enllaç amb el tiquet [cite: 222]
                'tipus' => 'ACUMULACIO', // Tipus de moviment [cite: 223]
                'punts_mov' => $puntsGuanyats, // Quantitat [cite: 224]
                'data_hora' => now(), // Timestamp del moment exacte [cite: 225]
            ]);

            // 7. Actualitzem el saldo del PERFIL de l'usuari 
            $perfil = Perfil::where('id_usuari', $usuari->id_usuari)->first();
            $perfil->punts_totals += $puntsGuanyats;
            $perfil->save();

            // 8. Confirmem que tot ha anat bé i tanquem la transacció
            DB::commit();

            return response()->json([
                'missatge' => 'Tiquet validat amb èxit!',
                'punts_guanyats' => $puntsGuanyats,
                'saldo_actual' => $perfil->punts_totals
            ], 200);

        } catch (\Exception $e) {
            // Si hi ha hagut algun error (ex: base de dades caiguda), desfem tots els canvis d'aquesta funció
            DB::rollBack(); 
            return response()->json(['missatge' => 'Error al processar el tiquet', 'error' => $e->getMessage()], 500);
        }
    }
    // Funció per a l'Usuari Estàndard: Bescanviar punts per una oferta
    public function bescanviarOferta(Request $request)
    {
        // 1. Validem que ens enviïn l'ID de l'oferta
        $request->validate([
            'id_oferta' => 'required|exists:ofertas,id_oferta'
        ]);

        $usuari = $request->user();
        $oferta = \App\Models\Oferta::findOrFail($request->id_oferta);
        $perfil = \App\Models\Perfil::where('id_usuari', $usuari->id_usuari)->first();

        // 2. Comprovem si l'usuari té prou punts [cite: 357]
        if ($perfil->punts_totals < $oferta->cost_punts) {
            return response()->json(['missatge' => 'No tens suficients punts per aquesta oferta.'], 400);
        }

        // 3. Obrim una Transacció de BD per seguretat
        try {
            DB::beginTransaction();

            // 4. Restem els punts al perfil de l'usuari
            $perfil->punts_totals -= $oferta->cost_punts;
            $perfil->save();

            // 5. Creem la transacció de tipus BESCANVI
            $transaccio = Transaccio::create([
                'id_usuari' => $usuari->id_usuari,
                'id_comerc' => $oferta->id_comerc,
                'id_oferta' => $oferta->id_oferta,
                'tipus' => 'BESCANVI',
                'punts_mov' => $oferta->cost_punts,
                'data_hora' => now(),
            ]);

            DB::commit();

            // 6. Generem un codi aleatori perquè el botiguer el validi després 
            $codiValidacio = 'NEXE-' . strtoupper(Str::random(6));

            return response()->json([
                'missatge' => 'Bescanvi realitzat amb èxit!',
                'codi_validacio' => $codiValidacio,
                'punts_restants' => $perfil->punts_totals
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['missatge' => 'Error al bescanviar', 'error' => $e->getMessage()], 500);
        }
    }

    // Llistar les vendes d'un comerç perquè el botiguer pugui comprovar qui ha comprat l'oferta
    public function vendesComerc(Request $request)
    {
        $comerc = \App\Models\Comerc::where('id_usuari', $request->user()->id_usuari)->first();
        
        if (!$comerc) {
            return response()->json(['missatge' => 'No tens cap comerç actiu.'], 404);
        }

        // Retornem els bescanvis d'aquest comerç, incloent l'usuari que ho ha comprat i l'oferta
        $vendes = Transaccio::with(['usuari', 'oferta'])
                    ->where('id_comerc', $comerc->id_comerc)
                    ->where('tipus', 'BESCANVI')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($vendes);
    }
    
}