<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuari>
 */
class UsuariFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->name(),
            'correu' => $this->faker->unique()->safeEmail(),
            'contrasenya' => bcrypt('password'), // Ponemos una contraseña por defecto
            'rol' => $this->faker->randomElement(['ESTANDARD', 'COMERC', 'ADMIN']),
            'estat' => 'ACTIU',
            'codi_qr' => 'NX-' . strtoupper($this->faker->unique()->bothify('????????????????')),
        ];
    }
}
