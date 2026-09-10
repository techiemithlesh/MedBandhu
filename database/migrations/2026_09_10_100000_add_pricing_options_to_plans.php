<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bridging migration — half-yearly billing + perpetual-licence pricing.
 * Folded into create_saas_tables for clean fresh installs; this backfills
 * databases that already ran it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'price_half_yearly')) {
                $table->decimal('price_half_yearly', 10, 2)->default(0)->after('price_monthly');
            }
            if (! Schema::hasColumn('plans', 'price_perpetual')) {
                $table->decimal('price_perpetual', 10, 2)->nullable()->after('price_extra_branch');
            }
            if (! Schema::hasColumn('plans', 'price_amc')) {
                $table->decimal('price_amc', 10, 2)->nullable()->after('price_perpetual');
            }
        });

        // Add 'half_yearly' to the billing_cycle enum (MySQL).
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY billing_cycle ENUM('monthly','half_yearly','yearly') NOT NULL DEFAULT 'yearly'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY billing_cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'yearly'");
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['price_half_yearly', 'price_perpetual', 'price_amc']);
        });
    }
};
