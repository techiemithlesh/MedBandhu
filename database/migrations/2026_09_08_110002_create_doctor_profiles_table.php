<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('specialization')->nullable();
            $table->string('qualifications')->nullable();          // e.g. "MBBS, MD (Medicine)"
            $table->string('registration_no')->nullable();
            $table->string('registration_council')->nullable();    // e.g. "Bihar Medical Council"
            $table->unsignedTinyInteger('experience_years')->nullable();

            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('followup_fee', 10, 2)->default(0);
            $table->unsignedSmallInteger('followup_valid_days')->default(7);
            $table->unsignedSmallInteger('appointment_duration_min')->default(15);

            $table->boolean('is_surgeon')->default(false);
            $table->boolean('online_consultation')->default(false);
            $table->text('bio')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
