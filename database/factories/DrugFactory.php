<?php

namespace Database\Factories;

use App\Models\Drug;
use App\Models\User; 
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugFactory extends Factory
{
    protected $model = Drug::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'brand' => $this->faker->company,
            'price' => $this->faker->numberBetween(10, 100),
            'category' => $this->faker->word,
            'dosage' => $this->faker->word,
            'stock' => $this->faker->numberBetween(1, 100),
            'image' => $this->faker->imageUrl(),
          
            'created_by' => User::factory(), 
        ];
    }
}
