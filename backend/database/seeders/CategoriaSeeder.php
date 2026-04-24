<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Restauració', 'icona' => '🍴', 'subs' => ['Restaurants', 'Bars', 'Cafeteries', 'Menjar per emportar']],
            ['nom' => 'Moda', 'icona' => '👕', 'subs' => ['Roba home', 'Roba dona', 'Calçat', 'Complements']],
            ['nom' => 'Alimentació', 'icona' => '🍎', 'subs' => ['Supermercats', 'Fruites i Verdures', 'Fleques', 'Carnisseries']],
            ['nom' => 'Electrònica', 'icona' => '📱', 'subs' => ['Informàtica', 'Telefonia', 'Electrodomèstics']],
            ['nom' => 'Llar', 'icona' => '🏠', 'subs' => ['Mobles', 'Decoració', 'Jardineria']],
            ['nom' => 'Salut', 'icona' => '⚕️', 'subs' => ['Farmàcies', 'Òptiques', 'Dentistes']],
        ];

        foreach ($categories as $catData) {
            $pare = Categoria::updateOrCreate(
                ['nom_cat' => $catData['nom']],
                ['icona' => $catData['icona']]
            );

            foreach ($catData['subs'] as $subNom) {
                Categoria::updateOrCreate(
                    ['nom_cat' => $subNom, 'parent_id' => $pare->id_categoria]
                );
            }
        }
    }
}