<?php

namespace Database\Seeders;

use App\Models\Comerc;
use App\Models\Oferta;
use Illuminate\Database\Seeder;

class OfertaSeeder extends Seeder
{
    public function run(): void
    {
        $comercs = Comerc::orderBy('id_comerc', 'asc')->get();

        $ofertes_per_comerc = [
            // 1. Fleca i Pastisseria del Barri
            'Fleca i Pastisseria del Barri' => [
                [
                    'titol' => 'Croissant calent + Cafè',
                    'descripcio' => 'El millor esmorzar del barri. Canvia els teus punts per un croissant acabat de fer i un cafè espresso o amb llet.',
                    'cost_punts' => 30,
                    'imatge' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Barra de pa tradicional de regal',
                    'descripcio' => 'Bescanvia aquesta oferta per emportar-te una barra de pa de pagès o de llenya totalment gratis en fer una compra mínima de 2€.',
                    'cost_punts' => 15,
                    'imatge' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Capsa de pastissets variats',
                    'descripcio' => 'Ideal per diumenges i celebracions. Capsa amb 6 peces de pastisseria artesana a escollir.',
                    'cost_punts' => 120,
                    'imatge' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 2. Restaurant El Racó Gastronòmic
            'Restaurant El Racó Gastronòmic' => [
                [
                    'titol' => 'Segon plat gratuït al menú',
                    'descripcio' => 'Gaudeix d\'un segon plat de cortesia en demanar un menú de migdia de dilluns a dijous.',
                    'cost_punts' => 100,
                    'imatge' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Ampolla de vi de la casa',
                    'descripcio' => 'Acompanya el teu sopar de cap de setmana amb una de les nostres ampolles de vi DO Costers del Segre.',
                    'cost_punts' => 60,
                    'imatge' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Postres gourmet a escollir',
                    'descripcio' => 'Endolceix el teu dinar. Bescanvia aquesta oferta per qualsevol dels nostres postres casolans elaborats diàriament.',
                    'cost_punts' => 35,
                    'imatge' => 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 3. Cafeteria L\'Aroma Cafè
            'Cafeteria L\'Aroma Cafè' => [
                [
                    'titol' => 'Cafè capuccino extra gran',
                    'descripcio' => 'El nostre capuccino estrella preparat amb llet de granja fresca o civada, empolsat amb cacau pur.',
                    'cost_punts' => 25,
                    'imatge' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Tros de pastís de pastanaga',
                    'descripcio' => 'Porció generosa del nostre pastís casolà de pastanaga i crema de formatge suau.',
                    'cost_punts' => 45,
                    'imatge' => 'https://images.unsplash.com/photo-1508737027454-e6454ef45afd?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Te matcha fred orgànic',
                    'descripcio' => 'Refrescant beguda de te matcha japonès ceremonial, ideal per recuperar energia.',
                    'cost_punts' => 30,
                    'imatge' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 4. Moda Jove - Carrer Major
            'Moda Jove - Carrer Major' => [
                [
                    'titol' => '15% de descompte en jaquetes',
                    'descripcio' => 'Aplicable a qualsevol jaqueta de temporada, incloent peces de cuir, texanes o abrics d\'hivern.',
                    'cost_punts' => 80,
                    'imatge' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Samarreta bàsica de cotó eco',
                    'descripcio' => 'Bescanvia els teus punts per una samarreta 100% de cotó orgànic certificada, disponible en diversos colors.',
                    'cost_punts' => 50,
                    'imatge' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Mitjons de disseny exclusiu',
                    'descripcio' => 'Un parell de mitjons amb motius divertits o minimalistes d\'alta qualitat fabricats al país.',
                    'cost_punts' => 20,
                    'imatge' => 'https://images.unsplash.com/photo-1582966772680-860e372bb558?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 5. Sabateria Pas a Pas
            'Sabateria Pas a Pas' => [
                [
                    'titol' => 'Netejador i raspall de calçat',
                    'descripcio' => 'Set de manteniment professional per a sabates de pell o camussa. Manté el teu calçat nou.',
                    'cost_punts' => 25,
                    'imatge' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => '20% de descompte en esportives',
                    'descripcio' => 'Estalvia en la teva propera compra de calçat esportiu d\'atletisme, running o casual.',
                    'cost_punts' => 110,
                    'imatge' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Plantilles de gel anatòmiques',
                    'descripcio' => 'Màxim confort per als teus peus quotidians. Es retallen a la teva mida exacta.',
                    'cost_punts' => 35,
                    'imatge' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 6. Informàtica Lleida
            'Informàtica Lleida' => [
                [
                    'titol' => 'Neteja física interna de portàtil',
                    'descripcio' => 'Millora el rendiment i evita sobreescalfaments. Inclou neteja de ventiladors i canvi de pasta tèrmica.',
                    'cost_punts' => 90,
                    'imatge' => 'https://images.unsplash.com/photo-1588508065123-287b28e013da?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Pendrive USB de 64GB ultra ràpid',
                    'descripcio' => 'Memòria flash USB 3.2 de gran capacitat i resistent, ideal per guardar documents i fitxers.',
                    'cost_punts' => 45,
                    'imatge' => 'https://images.unsplash.com/photo-1622760825370-d79047970d4d?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Descompte de 15€ en reparacions',
                    'descripcio' => 'Estalvia en qualsevol reparació de maquinari o eliminació de virus al nostre taller informàtic.',
                    'cost_punts' => 75,
                    'imatge' => 'https://images.unsplash.com/photo-1597872200319-3814819c8047?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 7. Òptica Claravisió
            'Òptica Claravisió' => [
                [
                    'titol' => 'Líquid per a lents de contacte',
                    'descripcio' => 'Solució única per a la neteja, desinfecció i conservació de tot tipus de lents de contacte blanes.',
                    'cost_punts' => 30,
                    'imatge' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Cordó de disseny per a ulleres',
                    'descripcio' => 'Afegeix un toc d\'estil i seguretat a les teves ulleres amb els nostres cordons de cotó de colors.',
                    'cost_punts' => 15,
                    'imatge' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Descompte de 30€ en vidres',
                    'descripcio' => 'Vàlid per a qualsevol canvi de vidres graduats mono-focals o progressius d\'última tecnologia.',
                    'cost_punts' => 120,
                    'imatge' => 'https://images.unsplash.com/photo-1591076482161-42ce6da69f67?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 8. Supermercat Ecològic
            'Supermercat Ecològic' => [
                [
                    'titol' => 'Bossa de roba reutilitzable',
                    'descripcio' => 'Una bossa resistent feta de cotó reciclat amb un disseny exclusiu per fer la teva compra diària.',
                    'cost_punts' => 10,
                    'imatge' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Ampolla d\'oli d\'oliva verge extra eco',
                    'descripcio' => 'Oli d\'oliva verge extra de premsat en fred, provinent de camps ecològics de les terres de Lleida.',
                    'cost_punts' => 60,
                    'imatge' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Pack de 1kg de taronges eco',
                    'descripcio' => 'Taronges d\'hort ecològic local, plenes de suculència i vitamina C natural.',
                    'cost_punts' => 25,
                    'imatge' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 9. Mobles Disseny i Llar
            'Mobles Disseny i Llar' => [
                [
                    'titol' => 'Coixí decoratiu per a sofà',
                    'descripcio' => 'Coixí amb teixit de lli suau de 45x45 cm disponible en colors pastís per complementar el teu sofà.',
                    'cost_punts' => 40,
                    'imatge' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Vela perfumada aromàtica premium',
                    'descripcio' => 'Elaborada amb cera de soja 100% natural, amb flaires de lavanda o fusta, ideal per crear llar.',
                    'cost_punts' => 20,
                    'imatge' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Val de descompte de 50€',
                    'descripcio' => 'Bescanvia els teus punts per un descompte directe en sofàs, taules de menjador o llits de disseny.',
                    'cost_punts' => 150,
                    'imatge' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=800'
                ]
            ],
            // 10. Floristeria i Jardí Lleida
            'Floristeria i Jardí Lleida' => [
                [
                    'titol' => 'Planta de test verda',
                    'descripcio' => 'Emporta\'t una planta fàcil de cuidar de la varietat Pothos o Cinta en test de ceràmica bàsic.',
                    'cost_punts' => 35,
                    'imatge' => 'https://images.unsplash.com/photo-1545241047-6083a3684587?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Ram de 3 roses fresques vermelles',
                    'descripcio' => 'Detall bonic i romàntic. Tres roses fresques vermelles ornamentades amb fulles verdes elegants.',
                    'cost_punts' => 50,
                    'imatge' => 'https://images.unsplash.com/photo-1559563458-527298c27178?auto=format&fit=crop&q=80&w=800'
                ],
                [
                    'titol' => 'Fertilitzant líquid universal (1L)',
                    'descripcio' => 'Nutrient mineral per a tot tipus de plantes d\'interior o exterior. Fes créixer el teu jardí de casa.',
                    'cost_punts' => 25,
                    'imatge' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&q=80&w=800'
                ]
            ]
        ];

        foreach ($comercs as $comerc) {
            $nomComerc = $comerc->nom_comercial;
            
            // Si tenim ofertes predefinides per a aquest comerç específic, les usem
            $llistaOfertes = isset($ofertes_per_comerc[$nomComerc]) 
                ? $ofertes_per_comerc[$nomComerc] 
                : [
                    [
                        'titol' => 'Oferta Especial a ' . $nomComerc,
                        'descripcio' => 'Gaudeix d\'una promoció única canviant els teus punts acumulats.',
                        'cost_punts' => rand(30, 90),
                        'imatge' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800'
                    ]
                ];

            foreach ($llistaOfertes as $dadesOferta) {
                Oferta::create([
                    'id_comerc' => $comerc->id_comerc,
                    'titol' => $dadesOferta['titol'],
                    'descripcio' => $dadesOferta['descripcio'],
                    'cost_punts' => $dadesOferta['cost_punts'],
                    'imatge' => $dadesOferta['imatge'],
                    'estat' => 1,
                    'data_publicacio' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
