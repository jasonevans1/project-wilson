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
        Schema::create('asset_replacement_events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Asset::class)->constrained()->cascadeOnDelete();
            $table->date('installed_at');
            $table->decimal('cost', 10, 2)->nullable();
            $table->unsignedSmallInteger('expected_lifespan_years')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'installed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_replacement_events');
    }
};
