<?php

namespace Database\Factories;

use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacistFactory extends Factory
{
    protected $model = Pharmacist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'license_image' => $this->faker->optional()->imageUrl(),
        ];
    }

    /**
     * Create an approved pharmacist.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved'
        ]);
    }
}
