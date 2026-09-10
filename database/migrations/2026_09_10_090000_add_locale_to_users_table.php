<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bridging migration — `locale` is folded into the original
 * add_tenant_and_profile_columns_to_users_table migration for a clean history;
 * this backfills it on databases that already ran that one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('locale', 5)->nullable()->after('last_login_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
    }
};
