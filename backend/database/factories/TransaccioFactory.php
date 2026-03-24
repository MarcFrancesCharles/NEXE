<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaccio>
 */
class TransaccioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipus' => $this->faker->randomElement(['ACUMULACIO', 'BESCANVI']),
            'punts_mov' => $this->faker->numberBetween(5, 100),
            'data_hora' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
