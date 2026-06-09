<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->enum('data_type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
