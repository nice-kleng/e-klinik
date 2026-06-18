<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->integer('systolic')->nullable();
            $table->integer('diastolic')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->integer('gcs')->nullable();
            $table->integer('blood_glucose')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->unsignedTinyInteger('pain_scale')->nullable();
            $table->text('allergy_notes')->nullable();
            $table->boolean('fall_risk')->nullable();
            $table->string('nutrition_status', 50)->nullable();
            $table->string('smoking_status', 50)->nullable();
            $table->string('pregnancy_status', 50)->nullable();
            $table->foreignId('triage_by')->constrained('users');
            $table->dateTime('triage_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage');
    }
};
