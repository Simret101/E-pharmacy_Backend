<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'order_id' => Order::factory(),
            'payment_method' => $this->faker->randomElement(['card', 'paypal', 'bank_transfer']),
            'payment_status' => 'completed',
            'payer_id' => User::factory(),
            'payer_email' => $this->faker->safeEmail,
            'currency' => 'USD',
        ];
    }
}