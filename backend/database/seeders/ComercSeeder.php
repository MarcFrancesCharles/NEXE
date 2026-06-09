<?php

namespace Database\Seeders;

use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\Categoria;
use Illuminate\Database\Seeder;

class ComercSeeder extends Seeder
{
    public function run(): void
    {
        $comerc_users = Usuari::where('rol', 'COMERC')->orderBy('id_usuari', 'asc')->get();
        $categories = Categoria::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No hi ha categories. Executa CategoriaSeeder primer.');
            return;
        }

        $lleida_locations = [
            [
                'nom' => 'Fleca i Pastisseria del Barri',
                'lat' => 41.6150,
                'lng' => 0.6248,
                'desc' => 'Forn de pa artesà des de 1950. Treballem amb blat de proximitat i llevat natural.',
                'categoria_nom' => 'Fleques',
                'imatge_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Restaurant El Racó Gastronòmic',
                'lat' => 41.6165,
                'lng' => 0.6272,
                'desc' => 'Menjar tradicional català amb ingredients frescos del mercat local. Ambient acollidor.',
                'categoria_nom' => 'Restaurants',
                'imatge_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Cafeteria L\'Aroma Cafè',
                'lat' => 41.6215,
                'lng' => 0.6221,
                'desc' => 'El millor cafè d\'especialitat a Lleida. Brioixeria artesanal i berenars deliciosos.',
                'categoria_nom' => 'Cafeteries',
                'imatge_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Moda Jove - Carrer Major',
                'lat' => 41.6170,
                'lng' => 0.6250,
                'desc' => 'Roba d\'home i complements amb les darreres tendències urbanes i de disseny.',
                'categoria_nom' => 'Roba home',
                'imatge_url' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Sabateria Pas a Pas',
                'lat' => 41.6238,
                'lng' => 0.6212,
                'desc' => 'Calçat per a tota la família. Comoditat, estil i primeres marques a bon preu.',
                'categoria_nom' => 'Calçat',
                'imatge_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Informàtica Lleida',
                'lat' => 41.6185,
                'lng' => 0.6230,
                'desc' => 'Reparació d\'ordinadors, venda de dispositius mòbils, ordinadors a mida i accessoris.',
                'categoria_nom' => 'Informàtica',
                'imatge_url' => 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Òptica Claravisió',
                'lat' => 41.6190,
                'lng' => 0.6265,
                'desc' => 'Cuidem de la teva salut visual. Ulleres de sol, graduades i lents de contacte.',
                'categoria_nom' => 'Òptiques',
                'imatge_url' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Supermercat Ecològic',
                'lat' => 41.6130,
                'lng' => 0.6280,
                'desc' => 'Alimentació 100% ecològica, productes de proximitat, frescos i a granel.',
                'categoria_nom' => 'Supermercats',
                'imatge_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Mobles Disseny i Llar',
                'lat' => 41.6250,
                'lng' => 0.6190,
                'desc' => 'Mobles de disseny modern per renovar el teu saló, habitació o cuina. Assessorament de decoració.',
                'categoria_nom' => 'Mobles',
                'imatge_url' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=800'
            ],
            [
                'nom' => 'Floristeria i Jardí Lleida',
                'lat' => 41.6145,
                'lng' => 0.6210,
                'desc' => 'Flors fresques, rams personalitzats per esdeveniments, plantes d\'interior i exterior.',
                'categoria_nom' => 'Jardineria',
                'imatge_url' => 'https://images.unsplash.com/photo-1587334274328-64186a80aeee?auto=format&fit=crop&q=80&w=800'
            ]
        ];

        foreach ($comerc_users as $index => $usuari) {
            $location = $lleida_locations[$index % count($lleida_locations)];
            
            // Busquem el ID de la categoria per nom
            $categoria = Categoria::where('nom_cat', $location['categoria_nom'])->first();
            $id_categoria = $categoria ? $categoria->id_categoria : $categories->first()->id_categoria;

            Comerc::updateOrCreate(
                ['id_usuari' => $usuari->id_usuari],
                [
                    'id_categoria' => $id_categoria,
                    'nom_comercial' => $location['nom'],
                    'cif' => 'B' . sprintf('%08d', 10000000 + $index),
                    'latitud' => $location['lat'],
                    'longitud' => $location['lng'],
                    'descripcio' => $location['desc'],
                    'telefon' => rand(600000000, 699999999),
                    'email_contacte' => $usuari->correu,
                    'enllac_web' => 'https://' . str_replace(' ', '', strtolower($location['nom'])) . '.cat',
                    'instagram' => str_replace(' ', '', strtolower($location['nom'])),
                    'imatge_url' => $location['imatge_url']
                ]
            );
        }
    }
}