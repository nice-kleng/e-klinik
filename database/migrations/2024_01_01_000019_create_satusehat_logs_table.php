<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satusehat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type');
            $table->string('resource_id')->nullable();
            $table->string('action');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('resource_type');
            $table->index('status');
            $table->index('synced_at');
            $table->index(['resource_type', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satusehat_logs');
    }
};
