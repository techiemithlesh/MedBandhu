<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bed occupancy timeline for an admission. The open row (ended_at null) is the
 * current bed; closed rows are transfer history and drive per-day bed charges.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_bed_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();

            $table->decimal('daily_charge', 10, 2)->default(0);   // snapshot of bed rate
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['ipd_admission_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_bed_movements');
    }
};
