<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('staff')->cascadeOnDelete();

            $table->text('chief_complaint')->nullable();
            $table->text('history_present_illness')->nullable();
            $table->text('examination_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('investigations_advised')->nullable();
            $table->text('advice')->nullable();
            $table->date('followup_date')->nullable();
            $table->text('private_notes')->nullable();

            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();

            $table->string('drug_name');
            $table->string('strength', 60)->nullable();       // e.g. "500 mg"
            $table->string('form', 40)->nullable();           // tablet / syrup / injection
            $table->string('dosage', 60)->nullable();         // e.g. "1-0-1"
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->string('instructions')->nullable();       // "after food"
            $table->unsignedSmallInteger('quantity')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('consultations');
    }
};
