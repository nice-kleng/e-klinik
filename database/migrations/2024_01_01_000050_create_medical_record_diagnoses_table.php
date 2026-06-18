<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('icd10_diagnosis_id')->constrained('icd10_diagnoses');
            $table->enum('type', ['primary', 'secondary'])->default('secondary');
            $table->text('notes')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['medical_record_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_diagnoses');
    }
};
