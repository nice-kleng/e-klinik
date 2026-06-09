<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_antrean', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('no_kartu', 50)->nullable();
            $table->string('no_antrean', 20)->nullable();
            $table->string('kode_poli', 20)->nullable();
            $table->string('kode_dokter', 20)->nullable();
            $table->string('nomor_sep', 50)->nullable();
            $table->string('status')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('sync_at')->nullable();
            $table->timestamps();

            $table->index('no_kartu');
            $table->index('no_antrean');
            $table->index('kode_poli');
            $table->index('nomor_sep');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_antrean');
    }
};
