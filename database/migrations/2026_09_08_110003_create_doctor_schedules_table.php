<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recurring weekly availability for a doctor at a branch. Feeds appointment
 * slot generation in Sprint 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();   // the doctor
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');   // 0 = Sunday .. 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_minutes')->default(15);
            $table->unsignedSmallInteger('max_tokens')->nullable();

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['hospital_id', 'staff_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
