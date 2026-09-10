<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row carries the whole OPD encounter lifecycle:
 *   scheduled → checked_in → in_consultation → completed
 * (or cancelled / no_show). A walk-in is created directly as checked_in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->string('appointment_no', 30);
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();      // null = unslotted walk-in
            $table->unsignedSmallInteger('slot_minutes')->default(15);
            $table->unsignedSmallInteger('token_no')->nullable();

            $table->enum('type', ['new', 'followup'])->default('new');
            $table->enum('source', ['booked', 'walk_in'])->default('booked');
            $table->enum('status', ['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'])
                ->default('scheduled');

            $table->string('reason')->nullable();            // chief complaint at booking
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false);

            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['hospital_id', 'appointment_no']);
            $table->index(['hospital_id', 'branch_id', 'scheduled_date', 'status']);
            $table->index(['doctor_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
