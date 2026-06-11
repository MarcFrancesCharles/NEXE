<?php

namespace Database\Seeders;

use App\Models\SolicitudTreball;
use Illuminate\Database\Seeder;

class SolicitudRolSeeder extends Seeder
{
    public function run(): void
    {
        // Sol·licitud per a administració (ADMIN)
        SolicitudTreball::create([
            'nom' => 'Sol·licitant Administrador',
            'correu' => 'solicitant.admin@example.com',
            'posicio' => 'ADMIN',
            'missatge' => 'Em vull postular com a administrador per ajudar a gestionar la plataforma.',
            'cv_path' => 'cvs/dummy_cv_admin.pdf',
            'estat' => 'PENDENT',
        ]);

        // Sol·licitud per a comerç (COMERC)
        SolicitudTreball::create([
            'nom' => 'Sol·licitant Comerç',
            'correu' => 'solicitant.comerc@example.com',
            'posicio' => 'COMERC',
            'missatge' => 'Vull obrir un comerç de proximitat associat a la plataforma NEXE.',
            'cv_path' => 'cvs/dummy_cv_comerc.pdf',
            'estat' => 'PENDENT',
        ]);
    }
}
