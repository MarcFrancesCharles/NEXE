<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comerc;
use App\Models\Oferta;

class OfertaSeeder extends Seeder
{
    public function run(): void
    {
        $comercos = Comerc::all();

        foreach ($comercos as $comerc) {
            // Cada comerç tindrà entre 2 i 5 ofertes
            Oferta::factory(rand(2, 5))->create([
                'id_comerc' => $comerc->id_comerc,
            ]);
        }
    }
}
