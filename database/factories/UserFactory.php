<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'status' => 'pending',
            'is_role' => 1,
            'phone' => fake()->phoneNumber,
            'address' => fake()->address,
            'lat' => fake()->latitude,
            'lng' => fake()->longitude,
            'pharmacy_name' => fake()->company,
            'tin_number' => fake()->numerify('#########'),
            'bank_name' => fake()->company,
            'account_number' => fake()->numerify('#########'),
            'license_image' => null,
            'tin_image' => null,
            'license_public_id' => null,
            'tin_public_id' => null,
            'google_id' => null
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_role' => 0,
            'status' => 'approved'
        ]);
    }

    /**
     * Create a pharmacist user.
     */
    public function pharmacist()
    {
        return $this->state([
            'is_role' => 2,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    
}
