<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Usuari;
use App\Models\Perfil;
use App\Models\Comerc;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creem Categories fixes
        $catRestauracio = Categoria::create(['nom_cat' => 'Restauració']);
        $catModa = Categoria::create(['nom_cat' => 'Moda i Complements']);
        $catServeis = Categoria::create(['nom_cat' => 'Serveis']);

        // 2. Creem un usuari amb rol COMERÇ de prova
        $usuariComerc = Usuari::create([
            'correu' => 'botiga@nexe.cat',
            'contrasenya' => Hash::make('12345678'),
            'rol' => 'COMERÇ',
            'estat' => 'ACTIU',
        ]);
        
        // Li creem el perfil (obligatori per la nostra integritat referencial)
        Perfil::create([
            'id_usuari' => $usuariComerc->id_usuari,
            'punts_totals' => 0,
        ]);

        // 3. Usem la factoria per generar 5 comerços assignats a aquest usuari i a categories aleatòries
        Comerc::factory(5)->create([
            'id_usuari' => $usuariComerc->id_usuari,
            // Assignem aleatòriament una de les 3 categories creades
            'id_categoria' => fn() => fake()->randomElement([
                $catRestauracio->id_categoria, 
                $catModa->id_categoria, 
                $catServeis->id_categoria
            ]),
        ]);
    }
}