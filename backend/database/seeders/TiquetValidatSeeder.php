<?php

namespace Database\Seeders;

use App\Models\TiquetValidat;
use App\Models\Usuari;
use App\Models\Comerc;
use Illuminate\Database\Seeder;

class TiquetValidatSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'ESTANDARD')->get();
        $comercs = Comerc::all();

        if ($usuaris->isEmpty() || $comercs->isEmpty()) return;

        TiquetValidat::factory()->count(10)->create();
    }
}
