<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('lab_test_categories')->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('specimen_type', 50)->nullable();
            $table->string('unit', 30)->nullable();
            $table->string('gender', 10)->nullable()->comment('L/P/null');
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->string('ref_range_low', 50)->nullable();
            $table->string('ref_range_high', 50)->nullable();
            $table->text('ref_range_text')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('loinc_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
