<?php

namespace Database\Factories;

use App\Models\InventoryLog;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryLogFactory extends Factory
{
    protected $model = InventoryLog::class;

    public function definition()
    {
        return [
            'drug_id' => Drug::factory(),
            'quantity' => $this->faker->numberBetween(1, 50),
            'action' => $this->faker->randomElement(['added', 'removed']),
            'description' => $this->faker->sentence,
        ];
    }
}