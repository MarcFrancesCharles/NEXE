<?php

namespace Database\Seeders;

use App\Models\Usuari;
use App\Models\SolicitudComerc;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SolicitudComercSeeder extends Seeder
{
    public function run(): void
    {
        $comerc_users = Usuari::where('rol', 'COMERC')->orderBy('id_usuari', 'asc')->get();
        $categories = Categoria::all();

        if ($categories->isEmpty()) return;

        // 1. Creem sol·licituds APROVADES pels 10 comerços actius
        foreach ($comerc_users as $index => $usuari) {
            $comerc = $usuari->comerc;
            if ($comerc) {
                SolicitudComerc::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_categoria' => $comerc->id_categoria,
                    'nom_comercial' => $comerc->nom_comercial,
                    'descripcio' => $comerc->descripcio,
                    'telefon' => $comerc->telefon,
                    'email_contacte' => $comerc->email_contacte,
                    'enllac_web' => $comerc->enllac_web,
                    'instagram' => $comerc->instagram,
                    'cif' => $comerc->cif,
                    'latitud' => $comerc->latitud,
                    'longitud' => $comerc->longitud,
                    'imatge_url' => $comerc->imatge_url,
                    'estat' => 'APROVADA',
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(10),
                ]);
            }
        }

        // 2. Creem 3 comerços extra en estat PENDENT i DENEGADA per fer proves des de l'admin
        $extra_data = [
            [
                'email' => 'comerc_pendent1@nexe.com',
                'nom_persona' => 'Marta Pendent Fleca',
                'nom_comercial' => 'Fleca de l\'Eixample',
                'descripcio' => 'Pastisseria artesanal tradicional catalana al bell mig de Lleida.',
                'estat' => 'PENDENT',
                'cif' => 'A98765432'
            ],
            [
                'email' => 'comerc_pendent2@nexe.com',
                'nom_persona' => 'Joan Pendent Electro',
                'nom_comercial' => 'Electrodomèstics Joan',
                'descripcio' => 'Botiga de barri dedicada a la venda i reparació de petits electrodomèstics.',
                'estat' => 'PENDENT',
                'cif' => 'B45678901'
            ],
            [
                'email' => 'comerc_denegat@nexe.com',
                'nom_persona' => 'Pere Denegat Bar',
                'nom_comercial' => 'Bar Las Vegas',
                'descripcio' => 'Bar nocturn i copes.',
                'estat' => 'DENEGADA',
                'cif' => 'C11223344'
            ],
        ];

        foreach ($extra_data as $index => $data) {
            $user = Usuari::create([
                'nom' => $data['nom_persona'],
                'correu' => $data['email'],
                'contrasenya' => Hash::make('password'),
                'rol' => 'COMERC',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-COMERC-EXTRA-' . $index,
            ]);

            SolicitudComerc::create([
                'id_usuari' => $user->id_usuari,
                'id_categoria' => $categories->random()->id_categoria,
                'nom_comercial' => $data['nom_comercial'],
                'descripcio' => $data['descripcio'],
                'telefon' => rand(600000000, 699999999),
                'email_contacte' => $data['email'],
                'cif' => $data['cif'],
                'latitud' => 41.61 + (rand(-10, 10) / 1000),
                'longitud' => 0.62 + (rand(-10, 10) / 1000),
                'imatge_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800',
                'estat' => $data['estat'],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);
        }
    }
}
