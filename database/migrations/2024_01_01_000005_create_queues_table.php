<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('queue_number');
            $table->date('queue_date');
            $table->enum('status', ['waiting', 'called', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->integer('estimated_wait_time')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('service_type', ['umum', 'gigi', 'kia', 'lansia', 'lainnya'])->default('umum');
            $table->string('bpjs_antrian_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['queue_date', 'polyclinic_id', 'status']);
            $table->index('queue_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
