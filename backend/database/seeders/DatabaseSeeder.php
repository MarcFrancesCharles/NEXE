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
        $this->call([
            CategoriaSeeder::class,
            PerfilSeeder::class,
            ComercSeeder::class,
            OfertaSeeder::class,
            TiquetValidatSeeder::class,
            SolAltaSeeder::class,
            TransaccioSeeder::class,
        ]);
    }
}