<?php

namespace Database\Seeders;

use App\Models\Usuari;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ADMINS
        // Specific Admin
        Usuari::updateOrCreate(
            ['correu' => 'admin@nexe.com'],
            [
                'nom' => 'Admin Nexe',
                'contrasenya' => Hash::make('admin123'),
                'rol' => 'ADMIN',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-ADMIN-SPECIFIC',
            ]
        );
        // 9 Random Admins
        for ($i = 1; $i <= 9; $i++) {
            Usuari::create([
                'nom' => 'Admin Auxiliar ' . $i,
                'correu' => 'admin' . $i . '@nexe.com',
                'contrasenya' => Hash::make('password'),
                'rol' => 'ADMIN',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-ADMIN-00' . $i,
            ]);
        }

        // 2. COMERC
        // Specific Comerc User
        Usuari::updateOrCreate(
            ['correu' => 'comerc@nexe.com'],
            [
                'nom' => 'Comerç de Prova',
                'contrasenya' => Hash::make('comerc123'),
                'rol' => 'COMERC',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-COMERC-SPECIFIC',
            ]
        );
        // 9 Random Comerc Users
        for ($i = 1; $i <= 9; $i++) {
            Usuari::create([
                'nom' => 'Comerç Usuari ' . $i,
                'correu' => 'comerc' . $i . '@nexe.com',
                'contrasenya' => Hash::make('password'),
                'rol' => 'COMERC',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-COMERC-00' . $i,
            ]);
        }

        // 3. ESTANDARD
        // Specific Standard User
        Usuari::updateOrCreate(
            ['correu' => 'usuari@nexe.com'],
            [
                'nom' => 'Usuari de Prova',
                'contrasenya' => Hash::make('usuari123'),
                'rol' => 'ESTANDARD',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-CLIENT-SPECIFIC',
            ]
        );
        // 9 Random Standard Users
        for ($i = 1; $i <= 9; $i++) {
            Usuari::create([
                'nom' => 'Usuari Estandard ' . $i,
                'correu' => 'usuari' . $i . '@nexe.com',
                'contrasenya' => Hash::make('password'),
                'rol' => 'ESTANDARD',
                'estat' => 'ACTIU',
                'codi_qr' => 'NX-CLIENT-00' . $i,
            ]);
        }
    }
}