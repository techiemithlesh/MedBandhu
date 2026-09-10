<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A pharmacy_sale is a dispensing event: either against a consultation's
 * prescription, or a walk-in counter sale. Stock is deducted per line via
 * medicine_batches (FEFO).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('sale_no', 30);
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prescribed_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('customer_name')->nullable();       // walk-in with no patient record
            $table->date('sale_date');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('round_off', 6, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('payment_mode', ['cash', 'card', 'upi', 'credit'])->default('cash');
            $table->enum('status', ['completed', 'cancelled'])->default('completed');

            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hospital_id', 'sale_no']);
            $table->index(['hospital_id', 'branch_id', 'sale_date']);
        });

        Schema::create('pharmacy_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacy_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_batch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('batch_number', 40)->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('mrp', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_sale_items');
        Schema::dropIfExists('pharmacy_sales');
    }
};
