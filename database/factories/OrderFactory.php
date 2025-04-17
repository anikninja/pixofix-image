<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => $this->generateOrderNumber(),
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['pending', 'claimed', 'completed']),
            'notes' => $this->faker->optional()->sentence(),
            'employee_id' => null,
            'category_id' => null,
        ];
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        return $this->faker->unique()->regexify('ORD-#[0-9]{4}');
    }
}
