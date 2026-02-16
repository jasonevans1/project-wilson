<?php

namespace Database\Seeders;

use App\Enums\AssetCategory;
use App\Models\AssetTemplate;
use App\Models\TemplateGroup;
use Illuminate\Database\Seeder;

class AssetTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            'kitchen' => [
                ['name' => 'Refrigerator', 'category' => AssetCategory::Appliance, 'location' => 'Kitchen', 'description' => 'Kitchen refrigerator/freezer unit'],
                ['name' => 'Dishwasher', 'category' => AssetCategory::Appliance, 'location' => 'Kitchen', 'description' => 'Built-in dishwasher'],
                ['name' => 'Oven/Range', 'category' => AssetCategory::Appliance, 'location' => 'Kitchen', 'description' => 'Oven and stovetop range'],
                ['name' => 'Microwave', 'category' => AssetCategory::Appliance, 'location' => 'Kitchen', 'description' => 'Countertop or built-in microwave'],
                ['name' => 'Garbage Disposal', 'category' => AssetCategory::Appliance, 'location' => 'Kitchen', 'description' => 'In-sink garbage disposal unit'],
                ['name' => 'Kitchen Faucet', 'category' => AssetCategory::Plumbing, 'location' => 'Kitchen', 'description' => 'Kitchen sink faucet'],
            ],
            'bathroom' => [
                ['name' => 'Water Heater', 'category' => AssetCategory::Plumbing, 'location' => 'Bathroom', 'description' => 'Hot water heater tank or tankless unit'],
                ['name' => 'Bathroom Exhaust Fan', 'category' => AssetCategory::Electrical, 'location' => 'Bathroom', 'description' => 'Ventilation exhaust fan'],
                ['name' => 'Toilet', 'category' => AssetCategory::Plumbing, 'location' => 'Bathroom', 'description' => 'Bathroom toilet'],
                ['name' => 'Bathroom Faucet', 'category' => AssetCategory::Plumbing, 'location' => 'Bathroom', 'description' => 'Bathroom sink faucet'],
                ['name' => 'Shower Head', 'category' => AssetCategory::Plumbing, 'location' => 'Bathroom', 'description' => 'Shower head and valve assembly'],
            ],
            'laundry-room' => [
                ['name' => 'Washing Machine', 'category' => AssetCategory::Appliance, 'location' => 'Laundry Room', 'description' => 'Clothes washing machine'],
                ['name' => 'Dryer', 'category' => AssetCategory::Appliance, 'location' => 'Laundry Room', 'description' => 'Clothes dryer'],
                ['name' => 'Laundry Sink', 'category' => AssetCategory::Plumbing, 'location' => 'Laundry Room', 'description' => 'Utility sink in laundry room'],
            ],
            'living-areas' => [
                ['name' => 'Ceiling Fan', 'category' => AssetCategory::Electrical, 'location' => 'Living Room', 'description' => 'Ceiling-mounted fan with light'],
                ['name' => 'Fireplace', 'category' => AssetCategory::Other, 'location' => 'Living Room', 'description' => 'Wood-burning or gas fireplace'],
                ['name' => 'Carpet/Flooring', 'category' => AssetCategory::Flooring, 'location' => 'Living Room', 'description' => 'Living area carpet or hardwood flooring'],
            ],
            'bedroom' => [
                ['name' => 'Bedroom Ceiling Fan', 'category' => AssetCategory::Electrical, 'location' => 'Bedroom', 'description' => 'Bedroom ceiling-mounted fan'],
                ['name' => 'Closet System', 'category' => AssetCategory::Other, 'location' => 'Bedroom', 'description' => 'Built-in closet organizer system'],
                ['name' => 'Bedroom Flooring', 'category' => AssetCategory::Flooring, 'location' => 'Bedroom', 'description' => 'Bedroom carpet or hardwood flooring'],
            ],
            'hvac-climate' => [
                ['name' => 'Central Air Conditioner', 'category' => AssetCategory::Hvac, 'location' => 'Exterior', 'description' => 'Central air conditioning compressor unit'],
                ['name' => 'Furnace', 'category' => AssetCategory::Hvac, 'location' => 'Basement', 'description' => 'Gas or electric furnace'],
                ['name' => 'Thermostat', 'category' => AssetCategory::Hvac, 'location' => 'Hallway', 'description' => 'Programmable or smart thermostat'],
                ['name' => 'Air Filter', 'category' => AssetCategory::Hvac, 'location' => 'Basement', 'description' => 'HVAC air filter for furnace or air handler'],
                ['name' => 'Humidifier', 'category' => AssetCategory::Hvac, 'location' => 'Basement', 'description' => 'Whole-house humidifier unit'],
            ],
            'electrical-safety' => [
                ['name' => 'Electrical Panel', 'category' => AssetCategory::Electrical, 'location' => 'Basement', 'description' => 'Main electrical breaker panel'],
                ['name' => 'Smoke Detector', 'category' => AssetCategory::Electrical, 'location' => 'Hallway', 'description' => 'Smoke and fire detector'],
                ['name' => 'Carbon Monoxide Detector', 'category' => AssetCategory::Electrical, 'location' => 'Hallway', 'description' => 'CO detector alarm'],
                ['name' => 'Sump Pump', 'category' => AssetCategory::Plumbing, 'location' => 'Basement', 'description' => 'Basement sump pump'],
            ],
            'exterior-garage' => [
                ['name' => 'Garage Door Opener', 'category' => AssetCategory::Electrical, 'location' => 'Garage', 'description' => 'Automatic garage door opener system'],
                ['name' => 'Roof', 'category' => AssetCategory::Roofing, 'location' => 'Exterior', 'description' => 'Home roofing system including shingles'],
                ['name' => 'Gutters', 'category' => AssetCategory::Exterior, 'location' => 'Exterior', 'description' => 'Rain gutters and downspouts'],
                ['name' => 'Deck/Patio', 'category' => AssetCategory::Exterior, 'location' => 'Backyard', 'description' => 'Outdoor deck or patio structure'],
                ['name' => 'Fence', 'category' => AssetCategory::Exterior, 'location' => 'Yard', 'description' => 'Perimeter fencing'],
                ['name' => 'Sprinkler System', 'category' => AssetCategory::Exterior, 'location' => 'Yard', 'description' => 'Lawn irrigation sprinkler system'],
            ],
        ];

        $groups = TemplateGroup::query()->pluck('id', 'slug');

        foreach ($templates as $groupSlug => $groupTemplates) {
            $groupId = $groups[$groupSlug];

            foreach ($groupTemplates as $order => $template) {
                AssetTemplate::query()->updateOrCreate(
                    [
                        'template_group_id' => $groupId,
                        'name' => $template['name'],
                    ],
                    [
                        ...$template,
                        'template_group_id' => $groupId,
                        'display_order' => $order + 1,
                    ],
                );
            }
        }
    }
}
