<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('called_by')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('call_sequence')->default(1);
            $table->timestamp('called_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['queue_id', 'call_sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_calls');
    }
};
