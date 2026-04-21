<?php

namespace Database\Seeders;

use App\Models\Usuari;
use App\Models\Perfil;
use Illuminate\Database\Seeder;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::all();

        foreach ($usuaris as $usuari) {
            // Només creem perfil si no en té (per evitar duplicats si runneja de nou)
            if (!$usuari->perfil) {
                Perfil::create([
                    'id_usuari' => $usuari->id_usuari,
                    'punts_totals' => rand(0, 500),
                    'imatge_url' => null,
                ]);
            }
        }
    }
}
