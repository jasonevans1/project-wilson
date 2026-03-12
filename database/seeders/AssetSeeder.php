<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Asset::factory()->create([
            'user_id' => $user->id,
            'name' => 'HVAC Unit',
            'install_date' => null,
            'expected_lifespan_years' => null,
        ]);

        Asset::factory()->create([
            'user_id' => $user->id,
            'name' => 'Dishwasher',
            'install_date' => null,
            'expected_lifespan_years' => null,
        ]);

        Asset::factory()->create([
            'user_id' => $user->id,
            'name' => 'Refrigerator',
            'install_date' => '2009-01-01',
            'expected_lifespan_years' => 10,
        ]);

        Asset::factory()->create([
            'user_id' => $user->id,
            'name' => 'Furnace',
            'install_date' => null,
            'expected_lifespan_years' => null,
        ]);

        Asset::factory()->archived()->create([
            'user_id' => $user->id,
            'name' => 'Old Microwave',
            'install_date' => null,
            'expected_lifespan_years' => null,
        ]);
    }
}
