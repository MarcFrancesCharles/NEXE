<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Restauració',
            'Moda i Complements',
            'Alimentació',
            'Electrònica',
            'Llar i Decoració',
            'Esports',
            'Salut i Bellesa',
            'Oci i Cultura',
            'Serveis Professionals',
            'Altres'
        ];

        foreach ($categories as $cat) {
            Categoria::firstOrCreate(['nom_cat' => $cat]);
        }
    }
}
