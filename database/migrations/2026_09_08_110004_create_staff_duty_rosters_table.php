<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_duty_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();

            $table->date('duty_date');
            $table->enum('shift', ['morning', 'evening', 'night', 'general', 'off'])->default('general');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->unique(['staff_id', 'duty_date']);
            $table->index(['hospital_id', 'branch_id', 'duty_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_duty_rosters');
    }
};
