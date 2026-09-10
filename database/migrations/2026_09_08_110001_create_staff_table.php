<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // login account, if any

            $table->string('employee_code', 30);
            $table->enum('type', [
                'doctor', 'nurse', 'pharmacist', 'receptionist',
                'lab_technician', 'radiologist', 'accountant', 'administrator', 'support',
            ])->default('support');

            $table->string('salutation', 10)->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group', 5)->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();

            $table->string('photo_path')->nullable();
            $table->string('designation')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'visiting', 'intern'])->default('permanent');
            $table->date('joined_on')->nullable();
            $table->date('left_on')->nullable();
            $table->enum('status', ['active', 'on_leave', 'suspended', 'resigned'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hospital_id', 'employee_code']);
            $table->index(['hospital_id', 'type', 'status']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_staff_id')->references('id')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_staff_id']);
        });

        Schema::dropIfExists('staff');
    }
};
