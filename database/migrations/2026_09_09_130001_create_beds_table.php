<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();

            $table->string('bed_number', 20);
            $table->string('room_label', 40)->nullable();
            $table->decimal('daily_charge', 10, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning', 'blocked'])->default('available');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ward_id', 'bed_number']);
            $table->index(['hospital_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
