<?php

namespace Database\Seeders;

use App\Models\Usuari;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador
        Usuari::updateOrCreate(
            ['correu' => 'admin@nexe.com'],
            [
                'nom' => 'Admin Nexe',
                'contrasenya' => Hash::make('admin123'),
                'rol' => 'ADMIN',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-ADMIN-001',
            ]
        );

        // Comerç de prova principal
        Usuari::updateOrCreate(
            ['correu' => 'comerc@nexe.com'],
            [
                'nom' => 'Comerç de Prova',
                'contrasenya' => Hash::make('comerc123'),
                'rol' => 'COMERC',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-COMERC-001',
            ]
        );

        // Usuari estàndard de prova
        Usuari::updateOrCreate(
            ['correu' => 'usuari@nexe.com'],
            [
                'nom' => 'Usuari de Prova',
                'contrasenya' => Hash::make('usuari123'),
                'rol' => 'ESTANDARD',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-CLIENT-001',
            ]
        );

        // Generem 4 usuaris addicionals FORÇANT el rol a 'COMERC' perquè el ComercSeeder pugui crear els 5 locals
        Usuari::factory()->count(4)->create([
            'rol' => 'COMERC',
            'estat' => 'ACTIU'
        ]);

        // Generem usuaris estàndards (clients) aleatoris per omplir la bd
        Usuari::factory()->count(10)->create([
            'rol' => 'ESTANDARD'
        ]);
    }
}