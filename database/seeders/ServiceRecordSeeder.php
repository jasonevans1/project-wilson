<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ServiceRecord;
use Illuminate\Database\Seeder;

class ServiceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Asset::whereHas('user')
            ->where('name', '!=', 'HVAC Unit')
            ->each(function (Asset $asset): void {
                ServiceRecord::factory()->create([
                    'user_id' => $asset->user_id,
                    'asset_id' => $asset->id,
                    'service_date' => '2025-06-15',
                ]);
            });

        $hvacUnit = Asset::where('name', 'HVAC Unit')->first();

        if ($hvacUnit) {
            ServiceRecord::factory()->create([
                'user_id' => $hvacUnit->user_id,
                'asset_id' => $hvacUnit->id,
                'service_date' => '2026-02-12',
            ]);
        }
    }
}
