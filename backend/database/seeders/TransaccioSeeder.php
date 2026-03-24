<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\Oferta;
use App\Models\TiquetValidat;
use App\Models\Transaccio;

class TransaccioSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'ESTANDARD')->get();
        $comercos = Comerc::all();
        $tiquets = TiquetValidat::all();
        
        if ($usuaris->isEmpty() || $comercos->isEmpty()) {
            return;
        }

        foreach ($usuaris as $usuari) {
            // Generem 5-10 transaccions per usuari
            for ($i = 0; $i < rand(5, 10); $i++) {
                $comerc = $comercos->random();
                $tipus = $this->faker()->randomElement(['ACUMULACIO', 'BESCANVI']);
                
                $data = [
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => $comerc->id_comerc,
                    'tipus' => $tipus,
                    'data_hora' => now()->subDays(rand(0, 365)),
                ];

                if ($tipus === 'ACUMULACIO') {
                    $tiquet = $tiquets->random();
                    $data['id_tiquet'] = $tiquet->id_tiquet;
                    $data['punts_mov'] = rand(10, 50);
                } else {
                    $oferta = $comerc->ofertes->random();
                    if ($oferta) {
                        $data['id_oferta'] = $oferta->id_oferta;
                        $data['punts_mov'] = -$oferta->cost_punts;
                    } else {
                        continue;
                    }
                }

                Transaccio::create($data);
            }
        }
    }

    private function faker()
    {
        return \Faker\Factory::create();
    }
}
