<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bridging migration — syncs an already-migrated DB (no-op on fresh installs). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            if (! Schema::hasColumn('hospitals', 'invoice_sequence')) {
                $table->unsignedBigInteger('invoice_sequence')->default(0)->after('pharmacy_sale_sequence');
            }
            if (! Schema::hasColumn('hospitals', 'payment_sequence')) {
                $table->unsignedBigInteger('payment_sequence')->default(0)->after('invoice_sequence');
            }
        });
    }

    public function down(): void
    {
        // columns belong to the base schema
    }
};
