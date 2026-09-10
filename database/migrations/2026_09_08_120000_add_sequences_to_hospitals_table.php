<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bridging migration: the sequence columns are folded into the original
 * create_hospitals_table for fresh installs; this syncs an already-migrated
 * database. Guarded so it is a no-op on fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            if (! Schema::hasColumn('hospitals', 'patient_sequence')) {
                $table->unsignedBigInteger('patient_sequence')->default(0)->after('settings');
            }
            if (! Schema::hasColumn('hospitals', 'appointment_sequence')) {
                $table->unsignedBigInteger('appointment_sequence')->default(0)->after('patient_sequence');
            }
        });
    }

    public function down(): void
    {
        // columns belong to the base schema; nothing to reverse here
    }
};
