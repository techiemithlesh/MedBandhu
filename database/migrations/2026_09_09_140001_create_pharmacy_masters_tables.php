<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['hospital_id', 'name']);
        });

        Schema::create('drug_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['hospital_id', 'name']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('drug_license_no', 40)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['hospital_id', 'name']);
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');                          // brand / trade name
            $table->string('generic_name')->nullable();
            $table->enum('form', ['tablet', 'capsule', 'syrup', 'injection', 'ointment', 'drops', 'inhaler', 'sachet', 'other'])
                ->default('tablet');
            $table->string('strength', 60)->nullable();       // "500 mg"
            $table->string('unit', 20)->default('unit');      // tablet / ml / vial
            $table->unsignedSmallInteger('pack_size')->default(1);   // units per pack (display)
            $table->string('hsn_code', 12)->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->enum('schedule', ['none', 'H', 'H1', 'X', 'OTC'])->default('none');
            $table->unsignedInteger('reorder_level')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hospital_id', 'name', 'strength']);
            $table->index(['hospital_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('drug_categories');
        Schema::dropIfExists('manufacturers');
    }
};
