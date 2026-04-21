<?php

namespace Database\Seeders;

use App\Models\Comerc;
use App\Models\Oferta;
use Illuminate\Database\Seeder;

class OfertaSeeder extends Seeder
{
    public function run(): void
    {
        $comercs = Comerc::all();

        foreach ($comercs as $comerc) {
            // Creem 2 o 3 ofertes per comerç
            $num_ofertas = rand(2, 3);
            
            for ($i = 0; $i < $num_ofertas; $i++) {
                Oferta::create([
                    'id_comerc' => $comerc->id_comerc,
                    'titol' => 'Oferta Especial ' . ($i + 1),
                    'cost_punts' => rand(50, 200),
                    'estat' => 1, // Activa
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
