<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();          // short tenant code, e.g. "APLO"
            $table->string('slug')->unique();

            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();

            $table->string('logo_path')->nullable();

            $table->enum('subscription_plan', ['trial', 'basic', 'pro', 'enterprise'])->default('trial'); // legacy stub, unused
            $table->enum('subscription_status', ['active', 'suspended', 'cancelled'])->default('active'); // legacy stub, unused
            $table->date('trial_ends_at')->nullable();

            // denormalised from the active subscription for the request hot-path
            $table->enum('access_status', ['active', 'restricted', 'blocked'])->default('active');
            $table->unsignedSmallInteger('branch_limit')->default(1);
            $table->string('custom_domain')->nullable()->unique();

            // enabled modules, branding overrides, misc per-tenant config
            $table->json('settings')->nullable();

            // per-tenant running counters for human-readable numbers
            $table->unsignedBigInteger('patient_sequence')->default(0);
            $table->unsignedBigInteger('appointment_sequence')->default(0);
            $table->unsignedBigInteger('ipd_sequence')->default(0);
            $table->unsignedBigInteger('purchase_sequence')->default(0);
            $table->unsignedBigInteger('pharmacy_sale_sequence')->default(0);
            $table->unsignedBigInteger('invoice_sequence')->default(0);
            $table->unsignedBigInteger('payment_sequence')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
