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

        Asset::factory(4)->create(['user_id' => $user->id]);
        Asset::factory()->archived()->create(['user_id' => $user->id]);
    }
}
