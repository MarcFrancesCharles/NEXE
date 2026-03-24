<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuari;
use App\Models\SolAlta;

class SolAltaSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'ESTANDARD')->get();

        if ($usuaris->isEmpty()) return;

        // Alguns usuaris han demanat ser comerç
        foreach ($usuaris->random(min(5, $usuaris->count())) as $usuari) {
            SolAlta::factory()->create([
                'id_usuari' => $usuari->id_usuari,
            ]);
        }
    }
}
