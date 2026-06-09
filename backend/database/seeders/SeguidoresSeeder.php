<?php

namespace Database\Seeders;

use App\Models\Usuari;
use App\Models\Comerc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeguidoresSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'ESTANDARD')->get();
        $comercs = Comerc::all();

        if ($usuaris->isEmpty() || $comercs->isEmpty()) return;

        foreach ($usuaris as $usuari) {
            // Cada usuari seguirà entre 2 i 5 comerços aleatoris
            $comercsSeguir = $comercs->random(rand(2, 5));

            foreach ($comercsSeguir as $comerc) {
                DB::table('seguidors')->insertOrIgnore([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => $comerc->id_comerc,
                    'created_at' => now()->subDays(rand(1, 15)),
                    'updated_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }
    }
}
