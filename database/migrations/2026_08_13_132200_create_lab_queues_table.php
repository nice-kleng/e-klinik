<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_request_id')->constrained()->cascadeOnDelete();
            $table->string('queue_number');
            $table->date('queue_date');
            $table->integer('queue_sequence')->default(0);
            $table->enum('status', ['waiting', 'called', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->foreignId('called_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['queue_date', 'status']);
            $table->index('queue_number');
            $table->index('registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_queues');
    }
};
