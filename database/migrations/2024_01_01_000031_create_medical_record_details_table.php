<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['diagnosis', 'procedure', 'observation', 'note']);
            $table->string('icd10_code', 10)->nullable();
            $table->string('icd10_name')->nullable();
            $table->string('icd9_code', 10)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('medical_record_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_details');
    }
};
