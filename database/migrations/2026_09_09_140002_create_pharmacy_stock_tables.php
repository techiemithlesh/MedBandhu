<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Goods receipt (purchase) header
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

            $table->string('purchase_no', 30);
            $table->string('invoice_number', 60)->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('received_date');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hospital_id', 'purchase_no']);
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();

            $table->string('batch_number', 40);
            $table->date('expiry_date');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('free_quantity')->default(0);
            $table->decimal('purchase_price', 10, 2);   // per unit
            $table->decimal('mrp', 10, 2);              // per unit
            $table->decimal('sale_price', 10, 2);       // per unit
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();
        });

        // Stock on hand — one row per (medicine, branch, batch)
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();

            $table->string('batch_number', 40);
            $table->date('expiry_date');
            $table->decimal('mrp', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->unsignedInteger('quantity_received');
            $table->integer('quantity_available');

            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'medicine_id', 'expiry_date'], 'med_batch_fefo_idx');
            $table->unique(['branch_id', 'medicine_id', 'batch_number'], 'med_batch_unique');
        });

        // Immutable stock ledger
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_batch_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['purchase', 'sale', 'sale_return', 'adjustment', 'expiry_writeoff']);
            $table->integer('quantity');                 // signed: + in, - out
            $table->integer('balance_after')->nullable();
            $table->nullableMorphs('reference');         // reference_type + reference_id
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'medicine_id'], 'stock_mov_med_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
