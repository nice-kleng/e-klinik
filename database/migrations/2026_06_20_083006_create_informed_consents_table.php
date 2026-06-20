<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informed_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('consent_type', 20); // general, procedure, surgery, anesthesia, transfusion, other
            $table->string('procedure_name')->nullable();
            $table->foreignId('procedure_icd9_id')->nullable()->constrained('icd9_cm_diagnoses')->nullOnDelete();
            $table->text('diagnosis')->nullable();
            $table->text('purpose')->nullable();
            $table->text('risks')->nullable();
            $table->text('benefits')->nullable();
            $table->text('alternatives')->nullable();
            $table->text('doctor_recommendation')->nullable();
            $table->string('patient_name')->nullable();
            $table->boolean('patient_agreed')->default(false);
            $table->timestamp('patient_signed_at')->nullable();
            $table->string('witness_name')->nullable();
            $table->string('status', 20)->default('draft'); // draft, signed, cancelled
            $table->string('signature_hash', 64)->nullable()->unique();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index(['medical_record_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informed_consents');
    }
};
