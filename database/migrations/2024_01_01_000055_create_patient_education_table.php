<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis_explained')->nullable();
            $table->text('medication_instructions')->nullable();
            $table->text('diet_instructions')->nullable();
            $table->text('activity_instructions')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->foreignId('educator_id')->constrained('users');
            $table->date('education_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_education');
    }
};
