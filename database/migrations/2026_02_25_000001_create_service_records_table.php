<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Asset::class)->constrained()->cascadeOnDelete();
            $table->date('service_date');
            $table->string('service_type');
            $table->text('description');
            $table->string('provider_name')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('under_warranty')->default(false);
            $table->date('warranty_expires_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
