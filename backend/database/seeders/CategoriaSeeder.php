<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Alimentació i Begudes', 'icona' => '🍎', 'subs' => [
                ['nom' => 'Supermercats i Queviures', 'desc' => 'Botigues de barri, alimentació general.'],
                ['nom' => 'Fleques i Pastisseries', 'desc' => 'Pa, dolços i cafeteries de degustació.'],
                ['nom' => 'Fruiteries i Verdures', 'desc' => 'Producte fresc i de proximitat.'],
                ['nom' => 'Celler i Begudes', 'desc' => 'Vins, licors i cerveses artesanes.']
            ]],
            ['nom' => 'Moda i Complements', 'icona' => '👗', 'subs' => [
                ['nom' => 'Roba i Tèxtil', 'desc' => 'Moda home, dona i infantil.'],
                ['nom' => 'Calçat', 'desc' => 'Sabateries.'],
                ['nom' => 'Joieria i Rellotgeria', 'desc' => 'Orfebreria i accessoris de luxe.'],
                ['nom' => 'Òptiques', 'desc' => 'Ulleres i salut ocular.']
            ]],
            ['nom' => 'Salut i Bellesa', 'icona' => '💄', 'subs' => [
                ['nom' => 'Farmàcies i Parafarmàcies', 'desc' => 'Salut i higiene.'],
                ['nom' => 'Perruqueries i Estètica', 'desc' => 'Cura personal i centres de bellesa.'],
                ['nom' => 'Gimnasos i Ioga', 'desc' => 'Benestar físic i esports.']
            ]],
            ['nom' => 'Llar i Decoració', 'icona' => '🏠', 'subs' => [
                ['nom' => 'Mobles i Interiorisme', 'desc' => 'Decoració, sofàs, llums.'],
                ['nom' => 'Ferreteries i Bricolatge', 'desc' => 'Eines i reparacions domèstiques.'],
                ['nom' => 'Floristeries i Jardineria', 'desc' => 'Plantes i flors.']
            ]],
            ['nom' => 'Tecnologia i Oci', 'icona' => '🔌', 'subs' => [
                ['nom' => 'Informàtica i Electrònica', 'desc' => 'Mòbils, ordinadors i reparacions.'],
                ['nom' => 'Llibreries i Papereries', 'desc' => 'Llibres, premsa i material d\'oficina.'],
                ['nom' => 'Joguineries i Regals', 'desc' => 'Jocs per a totes les edats.']
            ]],
            ['nom' => 'Serveis Professionals', 'icona' => '🛠️', 'subs' => [
                ['nom' => 'Tallers i Automoció', 'desc' => 'Reparació de cotxes, motos i bicicletes.'],
                ['nom' => 'Veterinaris i Mascotes', 'desc' => 'Tot per als animals.'],
                ['nom' => 'Bugaderies i Tintoreries', 'desc' => 'Neteja de tèxtil.']
            ]]
        ];

        foreach ($categories as $pare) {
            $catPare = Categoria::create(['nom_cat' => $pare['nom'], 'icona' => $pare['icona']]);
            foreach ($pare['subs'] as $sub) {
                Categoria::create([
                    'nom_cat' => $sub['nom'],
                    'descripcio' => $sub['desc'],
                    'parent_id' => $catPare->id_categoria
                ]);
            }
        }
    }
}