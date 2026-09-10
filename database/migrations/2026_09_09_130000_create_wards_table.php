<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['general', 'private', 'semi_private', 'icu', 'hdu', 'maternity', 'pediatric', 'emergency'])
                ->default('general');
            $table->string('floor', 40)->nullable();
            $table->enum('gender_restriction', ['any', 'male', 'female'])->default('any');
            $table->decimal('default_daily_charge', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
