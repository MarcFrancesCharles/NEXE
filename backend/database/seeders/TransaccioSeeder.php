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
            // Cada usuari tindrà entre 3 i 6 transaccions d'acumulació de punts
            $num_acumulacions = rand(3, 6);
            for ($i = 0; $i < $num_acumulacions; $i++) {
                $comerc = $comercs->random();
                Transaccio::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => $comerc->id_comerc,
                    'tipus' => 'ACUMULACIO',
                    'punts_mov' => rand(15, 80),
                    'data_hora' => now()->subDays(rand(5, 30)),
                ]);
            }

            // Cada usuari tindrà entre 1 i 3 transaccions de bescanvi d'ofertes
            $num_bescanvis = rand(1, 3);
            for ($j = 0; $j < $num_bescanvis; $j++) {
                $comerc = $comercs->random();
                $oferta = Oferta::where('id_comerc', $comerc->id_comerc)->inRandomOrder()->first();
                if ($oferta) {
                    Transaccio::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_comerc' => $comerc->id_comerc,
                        'id_oferta' => $oferta->id_oferta,
                        'tipus' => 'BESCANVI',
                        'punts_mov' => -$oferta->cost_punts,
                        'data_hora' => now()->subDays(rand(1, 5)),
                    ]);
                }
            }
        }
    }
}
