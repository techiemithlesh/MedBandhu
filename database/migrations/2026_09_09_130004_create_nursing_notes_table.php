<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->enum('category', ['general', 'observation', 'medication', 'procedure', 'diet'])->default('general');
            $table->text('note');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index(['ipd_admission_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_notes');
    }
};
