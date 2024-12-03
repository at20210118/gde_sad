<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Location;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place'=>fake()->word(),
            'event'=>fake()->word(),
            'description'=>fake()->sentence(),
            'event_start'=>fake()->dateTime(),
            'category_id'=>Category::factory(),
            'location_id'=>Location::factory()  
        ];
    }
}
