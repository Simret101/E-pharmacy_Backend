<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'pharmacist_id' => User::factory()->pharmacist(), // Create a pharmacist user
            'status' => 'pending',
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'shipping_address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'delivery_time' => $this->faker->dateTime,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'notes' => $this->faker->sentence,
            'prescription_status' => 'pending',
        ];
    }
}
