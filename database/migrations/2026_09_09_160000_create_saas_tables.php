<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -- bridge: sync an already-migrated hospitals table (no-op on fresh) --
        Schema::table('hospitals', function (Blueprint $table) {
            if (! Schema::hasColumn('hospitals', 'access_status')) {
                $table->enum('access_status', ['active', 'restricted', 'blocked'])->default('active')->after('trial_ends_at');
            }
            if (! Schema::hasColumn('hospitals', 'branch_limit')) {
                $table->unsignedSmallInteger('branch_limit')->default(1)->after('access_status');
            }
            if (! Schema::hasColumn('hospitals', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->unique()->after('branch_limit');
            }
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_half_yearly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->decimal('price_extra_branch', 10, 2)->default(0);   // per extra branch, stored as a YEARLY figure
            $table->decimal('price_perpetual', 10, 2)->nullable();      // one-time perpetual licence fee
            $table->decimal('price_amc', 10, 2)->nullable();            // yearly AMC for perpetual licences
            $table->unsignedSmallInteger('branch_limit')->default(1);
            $table->unsignedSmallInteger('trial_days')->default(14);
            $table->json('modules')->nullable();                        // null = all modules
            $table->json('features')->nullable();                       // {ai:bool, custom_domain:bool, sms:bool, priority_support:bool}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();

            $table->enum('licence_type', ['subscription', 'perpetual'])->default('subscription');
            $table->enum('billing_cycle', ['monthly', 'half_yearly', 'yearly'])->default('yearly');
            $table->enum('status', ['trialing', 'active', 'past_due', 'suspended', 'cancelled'])->default('trialing');

            $table->unsignedSmallInteger('branches')->default(1);        // contracted branch count
            $table->decimal('amount', 10, 2)->default(0);               // recurring amount for the current cycle
            $table->unsignedSmallInteger('grace_days')->default(7);

            $table->date('trial_ends_at')->nullable();
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->date('amc_valid_until')->nullable();                // perpetual licences
            $table->string('licence_key', 64)->nullable();

            $table->boolean('cancel_at_period_end')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();

            $table->string('number', 30)->unique();
            $table->string('description');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('due_date');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);                  // 18% GST on SaaS
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->enum('status', ['draft', 'sent', 'paid', 'void'])->default('sent');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_invoice_id')->constrained()->cascadeOnDelete();

            $table->string('number', 30)->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['razorpay', 'cash', 'upi', 'bank_transfer', 'cheque', 'adjustment'])->default('bank_transfer');
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_signature')->nullable();
            $table->string('reference')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payments');
        Schema::dropIfExists('platform_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
