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
            if (! Schema::hasColumn('hospitals', 'purchase_sequence')) {
                $table->unsignedBigInteger('purchase_sequence')->default(0)->after('ipd_sequence');
            }
            if (! Schema::hasColumn('hospitals', 'pharmacy_sale_sequence')) {
                $table->unsignedBigInteger('pharmacy_sale_sequence')->default(0)->after('purchase_sequence');
            }
        });
    }

    public function down(): void
    {
        // columns belong to the base schema
    }
};
