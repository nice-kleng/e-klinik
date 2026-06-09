<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->foreignId('category_id')->nullable()->index();
            $table->string('manufacturer')->nullable();
            $table->string('unit', 20);
            $table->string('content')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_generic')->default(false);
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
