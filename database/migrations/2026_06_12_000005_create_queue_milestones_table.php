<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('task_id');
            $table->string('task_name', 100);
            $table->timestamp('task_time');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['queue_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_milestones');
    }
};
