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
        Schema::create('maintenance_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_occurrence_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_type');
            $table->timestamp('sent_at')->nullable();
            $table->date('snoozed_until')->nullable();
            $table->unsignedInteger('snooze_count')->default(0);
            $table->timestamps();

            $table->unique(['maintenance_occurrence_id', 'reminder_type'], 'reminders_occurrence_type_unique');
            $table->index(['user_id', 'sent_at']);
            $table->index('snoozed_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reminders');
    }
};
