<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accrued charges against an admission. Bed charges are auto-generated from the
 * bed-movement timeline; other lines are added manually. Final settlement /
 * invoicing is Sprint 5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['bed', 'service', 'procedure', 'consumable', 'other'])->default('other');
            $table->date('charge_date');
            $table->string('description');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('amount', 12, 2)->default(0);

            $table->boolean('auto_generated')->default(false);      // bed charges
            $table->foreignId('bed_movement_id')->nullable()->constrained('ipd_bed_movements')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['ipd_admission_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_charges');
    }
};
