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
            if (! Schema::hasColumn('hospitals', 'ipd_sequence')) {
                $table->unsignedBigInteger('ipd_sequence')->default(0)->after('appointment_sequence');
            }
        });
    }

    public function down(): void
    {
        // column belongs to the base schema
    }
};
