<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('admission_no', 30);
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();      // current bed
            $table->foreignId('admitting_doctor_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete(); // if admitted from OPD

            $table->enum('source', ['opd', 'emergency', 'direct', 'referral'])->default('direct');
            $table->timestamp('admitted_at');
            $table->date('expected_discharge_on')->nullable();
            $table->text('provisional_diagnosis')->nullable();
            $table->text('admission_notes')->nullable();

            $table->string('attendant_name')->nullable();
            $table->string('attendant_phone', 20)->nullable();
            $table->string('attendant_relation', 40)->nullable();

            $table->enum('status', ['admitted', 'discharged', 'lama', 'expired', 'referred_out'])->default('admitted');
            $table->timestamp('discharged_at')->nullable();
            $table->enum('discharge_type', ['routine', 'lama', 'referral', 'expired'])->nullable();
            $table->text('discharge_summary')->nullable();
            $table->foreignId('discharge_doctor_id')->nullable()->constrained('staff')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hospital_id', 'admission_no']);
            $table->index(['hospital_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_admissions');
    }
};
