<?php

namespace Database\Seeders;

use App\Enums\ServiceType;
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
        Asset::all()->each(function (Asset $asset): void {
            foreach (ServiceType::cases() as $type) {
                ServiceRecord::factory()->create([
                    'user_id' => $asset->user_id,
                    'asset_id' => $asset->id,
                    'service_type' => $type->value,
                ]);
            }

            ServiceRecord::factory()->underWarranty()->create([
                'user_id' => $asset->user_id,
                'asset_id' => $asset->id,
            ]);
        });
    }
}
