<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuari;
use App\Models\Perfil;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $usuaris = Usuari::all();

        foreach ($usuaris as $usuari) {
            if (!$usuari->perfil) {
                Perfil::factory()->create([
                    'id_usuari' => $usuari->id_usuari,
                ]);
            }
        }
    }
}
