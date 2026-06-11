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
            $punts = ($usuari->correu === 'usuari@nexe.com') ? 1000 : rand(0, 500);

            // Només creem perfil si no en té (per evitar duplicats si runneja de nou)
            if (!$usuari->perfil) {
                Perfil::create([
                    'id_usuari' => $usuari->id_usuari,
                    'punts_totals' => $punts,
                    'imatge_url' => null,
                ]);
            } else {
                // Si ja té perfil, ens assegurem que l'usuari de prova tingui els 1000 punts
                if ($usuari->correu === 'usuari@nexe.com') {
                    $usuari->perfil->update(['punts_totals' => 1000]);
                }
            }
        }
    }
}
