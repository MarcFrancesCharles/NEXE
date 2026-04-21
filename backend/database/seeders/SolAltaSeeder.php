<?php

namespace Database\Seeders;

use App\Models\SolAlta;
use App\Models\Usuari;
use Illuminate\Database\Seeder;

class SolAltaSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::where('rol', 'COMERC')->get();

        if ($usuaris->isEmpty()) return;

        foreach ($usuaris as $usuari) {
            SolAlta::updateOrCreate(
                ['id_usuari' => $usuari->id_usuari],
                [
                    'dades_fiscals' => 'Dades fiscals de ' . $usuari->correu,
                    'estat' => 'APROVADA',
                ]
            );
        }
    }
}
