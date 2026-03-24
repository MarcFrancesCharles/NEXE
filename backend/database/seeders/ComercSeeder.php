<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuari;
use App\Models\Categoria;
use App\Models\Comerc;

class ComercSeeder extends Seeder
{
    public function run(): void
    {
        $usuarisComerc = Usuari::where('rol', 'COMERC')->get();
        $categories = Categoria::all();

        if ($usuarisComerc->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($usuarisComerc as $usuari) {
            // Cada usuari amb rol COMERC tindrà 1 o 2 comerços
            $numComercs = rand(1, 2);
            Comerc::factory($numComercs)->create([
                'id_usuari' => $usuari->id_usuari,
                'id_categoria' => $categories->random()->id_categoria,
            ]);
        }
    }
}
