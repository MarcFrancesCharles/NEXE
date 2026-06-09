<?php

namespace Database\Seeders;

use App\Models\Contacte;
use Illuminate\Database\Seeder;

class ContacteSeeder extends Seeder
{
    public function run(): void
    {
        $missatges = [
            [
                'nom' => 'María García',
                'email' => 'maria.garcia@example.com',
                'assumpte' => 'Error al bescanviar oferta',
                'missatge' => 'He intentat bescanviar l\'oferta de la sabateria però la càmera del comerç donava error de lectura de codi QR.',
                'estat' => 'pendent',
            ],
            [
                'nom' => 'Jordi Sanz',
                'email' => 'jordi.sanz@example.com',
                'assumpte' => 'Suggeriment per a la web',
                'missatge' => 'M\'agradaria que s\'afegís una opció de mapa interactiu per poder veure els comerços directament des del mòbil.',
                'estat' => 'resolt',
            ],
            [
                'nom' => 'Helena Soldevila',
                'email' => 'helena.sol@example.com',
                'assumpte' => 'Dubte amb els punts',
                'missatge' => 'Tinc un dubte: els punts tenen data de caducitat o es guarden per sempre al meu compte?',
                'estat' => 'pendent',
            ],
        ];

        foreach ($missatges as $m) {
            Contacte::create($m);
        }
    }
}
