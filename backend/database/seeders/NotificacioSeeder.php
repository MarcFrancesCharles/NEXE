<?php

namespace Database\Seeders;

use App\Models\Notificacio;
use App\Models\Usuari;
use App\Models\Comerc;
use App\Models\SolicitudComerc;
use Illuminate\Database\Seeder;

class NotificacioSeeder extends Seeder
{
    public function run(): void
    {
        $all_users = Usuari::all();
        $comercs = Comerc::all();
        $solicituds = SolicitudComerc::all();

        foreach ($all_users as $usuari) {
            // 1. NOTIFICACIONS PER A ADMINS
            if ($usuari->rol === 'ADMIN') {
                // Notificació de sol·licitud de comerç pendent de revisar
                $solicitudPendent = $solicituds->where('estat', 'PENDENT')->first();
                if ($solicitudPendent) {
                    Notificacio::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_comerc' => null,
                        'titol' => 'Sol·licitud de comerç pendent 🏢',
                        'missatge' => "El comerç '" . $solicitudPendent->nom_comercial . "' ha sol·licitat registrar-se. Revisa-la al panell de control.",
                        'icona' => '🏢',
                        'categoria' => 'solicituds',
                        'llegida' => false,
                    ]);
                }


            }

            // 2. NOTIFICACIONS PER A COMERÇOS
            elseif ($usuari->rol === 'COMERC') {
                // Mirem si l'usuari ja té una botiga física activa
                $comerc = $comercs->where('id_usuari', $usuari->id_usuari)->first();

                if ($comerc) {
                    // Notificacions per comerços actius
                    Notificacio::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_comerc' => $comerc->id_comerc,
                        'titol' => 'Nou seguidor! 👥',
                        'missatge' => 'Un nou client s\'ha subscrit a les teves novetats. Ja tens nous clients potencials!',
                        'icona' => '👥',
                        'categoria' => 'seguidors',
                        'llegida' => false,
                    ]);

                    Notificacio::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_comerc' => $comerc->id_comerc,
                        'titol' => 'Bescanvi completat! 🎁',
                        'missatge' => 'Un client ha bescanviat punts per una de les teves ofertes de forma segura.',
                        'icona' => '🎁',
                        'categoria' => 'vendes',
                        'llegida' => true,
                    ]);

                    Notificacio::create([
                        'id_usuari' => $usuari->id_usuari,
                        'id_comerc' => $comerc->id_comerc,
                        'titol' => 'Campanya de punts activa 📈',
                        'missatge' => 'La teva campanya de fidelització està tenint molta activitat aquesta setmana.',
                        'icona' => '📈',
                        'categoria' => 'estadistiques',
                        'llegida' => true,
                    ]);
                } else {
                    // Mirem si té sol·licitud pendent o denegada
                    $solicitud = $solicituds->where('id_usuari', $usuari->id_usuari)->first();
                    if ($solicitud) {
                        if ($solicitud->estat === 'PENDENT') {
                            Notificacio::create([
                                'id_usuari' => $usuari->id_usuari,
                                'id_comerc' => null,
                                'titol' => 'Sol·licitud en revisió ⏳',
                                'missatge' => 'La teva sol·licitud per registrar el comerç \'' . $solicitud->nom_comercial . '\' està sent revisada per l\'administració.',
                                'icona' => '⏳',
                                'categoria' => 'solicitud',
                                'llegida' => false,
                            ]);
                        } elseif ($solicitud->estat === 'DENEGADA') {
                            Notificacio::create([
                                'id_usuari' => $usuari->id_usuari,
                                'id_comerc' => null,
                                'titol' => 'Sol·licitud rebutjada ❌',
                                'missatge' => 'La teva sol·licitud per registrar el comerç \'' . $solicitud->nom_comercial . '\' ha estat denegada. Contacta amb suport@nexe.cat.',
                                'icona' => '❌',
                                'categoria' => 'solicitud',
                                'llegida' => false,
                            ]);
                        }
                    }
                }
            }

            // 3. NOTIFICACIONS PER A USUARIS ESTÀNDARDS
            elseif ($usuari->rol === 'ESTANDARD') {
                // Notificació de benvinguda
                Notificacio::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => null,
                    'titol' => 'Benvingut a NEXE! 🎉',
                    'missatge' => 'Comença a comprar als comerços de proximitat del teu barri per acumular punts i aconseguir premis.',
                    'icona' => '🎉',
                    'categoria' => 'benvinguda',
                    'llegida' => true,
                ]);

                // Notificació de punts atorgats
                Notificacio::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => null,
                    'titol' => 'Has guanyat punts! 💰',
                    'missatge' => 'S\'han afegit punts al teu perfil de NEXE per la teva darrera compra.',
                    'icona' => '💰',
                    'categoria' => 'punts',
                    'llegida' => false,
                ]);

                // Nova oferta en comerç que segueix
                $comercSeguit = $comercs->random();
                Notificacio::create([
                    'id_usuari' => $usuari->id_usuari,
                    'id_comerc' => $comercSeguit->id_comerc,
                    'titol' => 'Nova promoció al teu barri! 🏷️',
                    'missatge' => $comercSeguit->nom_comercial . ' acaba de publicar una nova oferta especial. No te la perdis!',
                    'icona' => '🏷️',
                    'categoria' => 'ofertes',
                    'llegida' => false,
                ]);
            }
        }
    }
}
