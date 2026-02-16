<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemplateGroup>
 */
class TemplateGroupFactory extends Factory
{
    /** @var list<string> */
    protected static array $groupNames = [
        'Kitchen', 'Bathroom', 'Laundry Room', 'Living Areas',
        'Bedroom', 'HVAC & Climate', 'Electrical & Safety', 'Exterior & Garage',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::$groupNames);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => null,
            'display_order' => 0,
        ];
    }
}
