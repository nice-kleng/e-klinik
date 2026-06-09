<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('sep_number', 50)->index();
            $table->string('claim_type')->nullable();
            $table->string('treatment_type')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('discharge_date')->nullable();
            $table->string('diagnosis_code', 10)->nullable();
            $table->string('procedure_code', 10)->nullable();
            $table->decimal('tariff', 15, 2)->default(0);
            $table->enum('status', ['submitted', 'verified', 'paid', 'rejected'])->default('submitted');
            $table->json('response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('claim_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_claims');
    }
};
