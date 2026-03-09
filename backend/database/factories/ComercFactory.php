<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComercFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Generem dades falses realistes per als nostres comerços de prova
            'nom_comercial' => fake()->company(),
            'cif' => fake()->unique()->bothify('B########'), // Format de CIF espanyol bàsic
            'coord_gps' => fake()->latitude() . ', ' . fake()->longitude(),
        ];
    }
}