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

        $lleida_locations = [
            ['nom' => 'Moda Lleida - Carrer Major', 'lat' => 41.6150, 'lng' => 0.6248, 'desc' => 'Ubicat al Carrer Major, 15'],
            ['nom' => 'Restauració Sant Joan', 'lat' => 41.6165, 'lng' => 0.6272, 'desc' => 'Plaça de Sant Joan, 2'],
            ['nom' => 'Cafeteria Ricard Viñes', 'lat' => 41.6215, 'lng' => 0.6221, 'desc' => 'Plaça Ricard Viñes, 5'],
            ['nom' => 'Llibreria del Carme', 'lat' => 41.6186, 'lng' => 0.6275, 'desc' => 'Carrer del Carme, 10'],
            ['nom' => 'Electrònica Prat de la Riba', 'lat' => 41.6238, 'lng' => 0.6212, 'desc' => 'Avinguda Prat de la Riba, 45'],
        ];

        foreach ($comerc_users as $index => $usuari) {
            if (!Comerc::where('id_usuari', $usuari->id_usuari)->exists()) {
                $location = $lleida_locations[$index % count($lleida_locations)];
                
                Comerc::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_categoria' => $categories->random()->id_categoria,
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
