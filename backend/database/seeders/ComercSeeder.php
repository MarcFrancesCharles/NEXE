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
        $comerc_users = Usuari::where('rol', 'COMERC')->get();
        $categories = Categoria::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No hi ha categories. Executa CategoriaSeeder primer.');
            return;
        }

        // Añadimos el campo 'categoria_nom' coincidiendo exactamente con los nombres del CategoriaSeeder
        $lleida_locations = [
            ['nom' => 'Moda Lleida - Carrer Major', 'lat' => 41.6150, 'lng' => 0.6248, 'desc' => 'Ubicat al Carrer Major, 15', 'categoria_nom' => 'Roba dona'],
            ['nom' => 'Restauració Sant Joan', 'lat' => 41.6165, 'lng' => 0.6272, 'desc' => 'Plaça de Sant Joan, 2', 'categoria_nom' => 'Restaurants'],
            ['nom' => 'Cafeteria Ricard Viñes', 'lat' => 41.6215, 'lng' => 0.6221, 'desc' => 'Plaça Ricard Viñes, 5', 'categoria_nom' => 'Cafeteries'],
            ['nom' => 'Sabateria Pas a Pas', 'lat' => 41.6170, 'lng' => 0.6250, 'desc' => 'Carrer Major, 40', 'categoria_nom' => 'Calçat'], // Tu ejemplo de la zapatería
            ['nom' => 'Electrònica Prat de la Riba', 'lat' => 41.6238, 'lng' => 0.6212, 'desc' => 'Avinguda Prat de la Riba, 45', 'categoria_nom' => 'Informàtica'],
        ];

        foreach ($comerc_users as $index => $usuari) {
            if (!Comerc::where('id_usuari', $usuari->id_usuari)->exists()) {
                $location = $lleida_locations[$index % count($lleida_locations)];
                
                // Buscamos el ID de la categoría por su nombre
                $categoria = Categoria::where('nom_cat', $location['categoria_nom'])->first();
                
                // Si por algún motivo no existe (error tipográfico), asignamos una por defecto
                $id_categoria = $categoria ? $categoria->id_categoria : $categories->first()->id_categoria;

                Comerc::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_categoria' => $id_categoria,
                    'nom_comercial' => $location['nom'],
                    'cif' => strtoupper(bin2hex(random_bytes(4))),
                    'latitud' => $location['lat'],
                    'longitud' => $location['lng'],
                    'descripcio' => $location['desc'],
                    'telefon' => rand(600000000, 999999999),
                    'email_contacte' => $usuari->correu,
                ]);
            }
        }
    }
}