<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satusehat_resources', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('resource_type');
            $table->string('resource_id_ss')->nullable();
            $table->string('version', 20)->nullable();
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('resource_type');
            $table->index('resource_id_ss');
            $table->index('status');
            $table->index(['resource_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satusehat_resources');
    }
};
