<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant + HR profile columns for users.
 *
 * Kept as a separate migration (not folded into the base create_users_table)
 * because the FK targets `hospitals`, which must be created first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null hospital_id => platform-level Super Admin
            $table->foreignId('hospital_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();

            $table->string('phone', 20)->nullable()->after('email');
            $table->string('designation')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('designation');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('locale', 5)->nullable()->after('last_login_at');
        });

        // A user can work across several branches of their hospital.
        Schema::create('branch_user', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['branch_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hospital_id');
            $table->dropColumn(['phone', 'designation', 'is_active', 'last_login_at', 'locale']);
        });
    }
};
