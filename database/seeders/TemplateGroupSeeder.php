<?php

namespace Database\Seeders;

use App\Models\TemplateGroup;
use Illuminate\Database\Seeder;

class TemplateGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['name' => 'Kitchen', 'slug' => 'kitchen', 'display_order' => 1],
            ['name' => 'Bathroom', 'slug' => 'bathroom', 'display_order' => 2],
            ['name' => 'Laundry Room', 'slug' => 'laundry-room', 'display_order' => 3],
            ['name' => 'Living Areas', 'slug' => 'living-areas', 'display_order' => 4],
            ['name' => 'Bedroom', 'slug' => 'bedroom', 'display_order' => 5],
            ['name' => 'HVAC & Climate', 'slug' => 'hvac-climate', 'display_order' => 6],
            ['name' => 'Electrical & Safety', 'slug' => 'electrical-safety', 'display_order' => 7],
            ['name' => 'Exterior & Garage', 'slug' => 'exterior-garage', 'display_order' => 8],
        ];

        foreach ($groups as $group) {
            TemplateGroup::query()->updateOrCreate(
                ['slug' => $group['slug']],
                $group,
            );
        }
    }
}
