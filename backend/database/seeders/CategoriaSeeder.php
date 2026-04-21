<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Restauració',
            'Moda',
            'Alimentació',
            'Electrònica',
            'Llar',
            'Esports',
            'Salut',
            'Oci',
            'Serveis',
            'Altres',
        ];

        foreach ($categories as $cat) {
            Categoria::updateOrCreate(['nom_cat' => $cat]);
        }
    }
}