<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SolAlta>
 */
class SolAltaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dades_fiscals' => $this->faker->text(),
            'estat' => $this->faker->randomElement(['PENDENT', 'APROVADA', 'DENEGADA']),
        ];
    }
}
