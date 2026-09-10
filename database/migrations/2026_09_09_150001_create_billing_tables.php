<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code', 20);
            $table->enum('category', ['consultation', 'procedure', 'investigation', 'nursing', 'room', 'package', 'misc'])
                ->default('misc');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hospital_id', 'code']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_no', 30);
            $table->enum('type', ['opd', 'ipd', 'general'])->default('general');
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ipd_admission_id')->nullable()->constrained()->nullOnDelete();

            $table->date('invoice_date');
            $table->enum('status', ['draft', 'finalized', 'partially_paid', 'paid', 'cancelled', 'refunded'])->default('draft');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('round_off', 6, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('notes')->nullable();

            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['hospital_id', 'invoice_no']);
            $table->index(['hospital_id', 'branch_id', 'invoice_date', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('item_type', ['service', 'consultation', 'bed', 'pharmacy', 'procedure', 'other'])->default('service');
            $table->nullableMorphs('source');       // ipd_charge, pharmacy_sale, ...
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->string('payment_no', 30);
            $table->enum('type', ['payment', 'refund'])->default('payment');
            $table->decimal('amount', 12, 2);
            $table->enum('mode', ['cash', 'card', 'upi', 'cheque', 'bank_transfer'])->default('cash');
            $table->string('reference')->nullable();
            $table->date('payment_date');
            $table->string('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['hospital_id', 'payment_no']);
            $table->index(['hospital_id', 'branch_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('services');
    }
};
