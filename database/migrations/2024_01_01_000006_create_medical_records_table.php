<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('polyclinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('queue_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->enum('visit_type', ['rawat_jalan', 'gawat_darurat', 'rawat_inap'])->default('rawat_jalan');
            $table->text('subjective_complaint')->nullable();
            $table->text('objective_finding')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->string('diagnosis_primary')->nullable();
            $table->json('diagnosis_secondary')->nullable();
            $table->text('anamnesis')->nullable();
            $table->text('physical_exam')->nullable();
            $table->json('vital_signs')->nullable();
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'visit_date']);
            $table->index('doctor_id');
            $table->index('polyclinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
