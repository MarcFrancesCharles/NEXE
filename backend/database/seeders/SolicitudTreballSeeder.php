<?php

namespace Database\Seeders;

use App\Models\SolicitudTreball;
use Illuminate\Database\Seeder;

class SolicitudTreballSeeder extends Seeder
{
    public function run(): void
    {
        $solicituds = [
            [
                'nom' => 'Laura Puigdevall',
                'correu' => 'laura.puig@example.com',
                'posicio' => 'Dependenta de Fleca',
                'missatge' => 'Tinc 3 anys d\'experiència en atenció al client i venda de productes de fleca i rebosteria.',
                'cv_path' => 'cvs/dummy_cv_1.pdf',
                'estat' => 'PENDENT',
            ],
            [
                'nom' => 'Albert Soler',
                'correu' => 'albert.soler@example.com',
                'posicio' => 'Cambrer de Sala',
                'missatge' => 'M\'agradaria formar part del vostre equip. Tinc experiència en servei de taula i barra.',
                'cv_path' => 'cvs/dummy_cv_2.pdf',
                'estat' => 'PENDENT',
            ],
            [
                'nom' => 'Marta Vila',
                'correu' => 'marta.vila@example.com',
                'posicio' => 'Ajudant de Cuina',
                'missatge' => 'Aprenent de cuina amb moltes ganes de treballar i especial interès per la cuina tradicional catalana.',
                'cv_path' => 'cvs/dummy_cv_3.pdf',
                'estat' => 'DENEGADA',
            ],
            [
                'nom' => 'Sílvia Camps',
                'correu' => 'silvia.camps@example.com',
                'posicio' => 'Dependenta de Moda',
                'missatge' => 'Passió per la moda i l\'assessorament personalitzat a clients. Parlo anglès i francès.',
                'cv_path' => 'cvs/dummy_cv_4.pdf',
                'estat' => 'APROVADA',
            ],
        ];

        foreach ($solicituds as $solicitud) {
            SolicitudTreball::create($solicitud);
        }
    }
}
