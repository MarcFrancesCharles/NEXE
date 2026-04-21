<?php

namespace Database\Seeders;

use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\Oferta;
use App\Models\Transaccio;
use Illuminate\Database\Seeder;

class TransaccioSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'ESTANDARD')->get();
        $comercs = Comerc::all();

        if ($usuaris->isEmpty() || $comercs->isEmpty()) {
            return;
        }

        foreach ($usuaris as $usuari) {
            // Un par de transacciones por usuario
            $comerc = $comercs->random();
            
            // Acumulació
            Transaccio::create([
                'id_usuari' => $usuari->id_usuari,
                'id_comerc' => $comerc->id_comerc,
                'tipus' => 'ACUMULACIO',
                'punts_mov' => rand(10, 50),
                'data_hora' => now()->subDays(rand(1, 10)),
            ]);

            // Bescanvi (si n'hi ha ofertes disponibles)
            $oferta = Oferta::where('id_comerc', $comerc->id_comerc)->first();
            if ($oferta) {
                Transaccio::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => $comerc->id_comerc,
                    'id_oferta' => $oferta->id_oferta,
                    'tipus' => 'BESCANVI',
                    'punts_mov' => -$oferta->cost_punts,
                    'data_hora' => now()->subDays(rand(0, 5)),
                ]);
            }
        }
    }
}
