<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained('lab_tests')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->string('result_value', 100)->nullable();
            $table->text('result_text')->nullable();
            $table->string('ref_range_low', 50)->nullable();
            $table->string('ref_range_high', 50)->nullable();
            $table->text('ref_range_text')->nullable();
            $table->string('unit', 30)->nullable();
            $table->enum('flag', ['normal', 'abnormal', 'critical', 'not_tested'])->default('not_tested');
            $table->text('notes')->nullable();
            $table->foreignId('examined_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('examined_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
