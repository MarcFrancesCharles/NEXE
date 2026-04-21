<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComercFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_usuari' => \App\Models\Usuari::factory(),
            'id_categoria' => \App\Models\Categoria::factory(),
            'nom_comercial' => $this->faker->company(),
            'cif' => $this->faker->unique()->bothify('B########'),
            'latitud' => $this->faker->latitude(),
            'longitud' => $this->faker->longitude(),
            'descripcio' => $this->faker->sentence(),
            'telefon' => $this->faker->numberBetween(600000000, 999999999),
            'email_contacte' => $this->faker->companyEmail(),
        ];
    }
}