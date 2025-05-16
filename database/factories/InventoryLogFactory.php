<?php

namespace Database\Factories;

use App\Models\InventoryLog;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryLogFactory extends Factory
{
    protected $model = InventoryLog::class;

    public function definition()
    {
        return [
            'drug_id' => Drug::factory(), // Create a related drug
            'user_id' => User::factory(), // Create a related user
            'change_type' => $this->faker->randomElement(['sale', 'restock', 'update', 'creation', 'deletion', 'stock_adjustment', 'stock_update']), // Match ENUM values
            'quantity_changed' => $this->faker->numberBetween(1, 100), // Quantity changed
            'reason' => $this->faker->sentence, // Reason for the change
        ];
    }
}