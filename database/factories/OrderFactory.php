<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'canceled']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }


    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            // Optionally create related OrderDetail records
            OrderDetail::factory()->count(2)->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
