<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();

            $table->string('uhid', 30);                 // unique hospital-wide patient id
            $table->foreignId('registered_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('salutation', 10)->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->boolean('dob_estimated')->default(false);   // age given, exact DOB unknown
            $table->string('blood_group', 5)->nullable();
            $table->enum('marital_status', ['single', 'married', 'other'])->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();

            $table->string('id_proof_type', 40)->nullable();
            $table->string('id_proof_number', 60)->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation', 40)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hospital_id', 'uhid']);
            $table->index(['hospital_id', 'phone']);
            $table->index(['hospital_id', 'first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
