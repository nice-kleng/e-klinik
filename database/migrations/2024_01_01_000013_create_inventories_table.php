<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number', 50)->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->date('production_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('batch_number');
            $table->index('expired_date');
            $table->index(['medicine_id', 'expired_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
