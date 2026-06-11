<?php

namespace Database\Factories;

use App\Models\Akta;
use App\Models\Klien;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Akta>
 */
class AktaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_klien' => Klien::factory(),
            'id_user' => User::factory(),
            'jenis_template' => fake()->randomElement(['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat']),
            'konten_teks_utama' => fake()->paragraphs(5, true),
            'status_workflow' => 'Draft',
            'tanggal_dibuat' => now(),
            'last_updated' => now(),
        ];
    }

    /**
     * Indicate that the akta is in Draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_workflow' => 'Draft',
        ]);
    }

    /**
     * Indicate that the akta is in Diverifikasi status.
     */
    public function diverifikasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_workflow' => 'Diverifikasi',
        ]);
    }

    /**
     * Indicate that the akta is in Final status.
     */
    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_workflow' => 'Final',
        ]);
    }

    /**
     * Indicate that the akta is in Selesai status.
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_workflow' => 'Selesai',
        ]);
    }
}
