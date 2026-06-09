<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('no_rm')->unique();
            $table->string('nik', 20)->unique();
            $table->string('no_kk', 20)->nullable();
            $table->string('name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date');
            $table->enum('gender', ['L', 'P']);
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->text('address')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('marriage_status')->nullable();
            $table->string('religion')->nullable();
            $table->string('insurance_type')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('bpjs_status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
