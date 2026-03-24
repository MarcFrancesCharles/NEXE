<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perfil>
 */
class PerfilFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'punts_totals' => $this->faker->numberBetween(0, 1000),
            'imatge_url' => $this->faker->optional()->imageUrl(200, 200, 'people'),
        ];
    }
}
