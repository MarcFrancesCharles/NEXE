<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TiquetValidat>
 */
class TiquetValidatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codi_qr' => $this->faker->unique()->sha256(),
            'import_compra' => $this->faker->randomFloat(2, 5, 200),
            'data_emissio' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
