<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Oferta>
 */
class OfertaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_comerc' => \App\Models\Comerc::factory(),
            'titol' => $this->faker->sentence(3),
            'descripcio' => $this->faker->paragraph(),
            'cost_punts' => $this->faker->numberBetween(10, 500),
            'estat' => $this->faker->boolean(80), // 80% chances of being active
            'data_fi' => $this->faker->optional(0.7)->dateTimeBetween('now', '+1 year'),
        ];
    }
}
