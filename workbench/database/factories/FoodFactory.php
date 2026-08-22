<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Workbench\App\Models\Food;

#[UseModel(Food::class)]
class FoodFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'price' => fake()->randomFloat(2, 1, 100),
            'category' => fake()->word(),
        ];
    }

}
