<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_seps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('queue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('no_sep', 50)->unique()->index();
            $table->string('no_kartu', 50)->index();
            $table->date('tgl_pelayanan');
            $table->string('kode_poli', 20);
            $table->string('kode_dokter', 20)->nullable();
            $table->string('diagnosa', 20)->nullable();
            $table->string('no_rujukan', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->string('status', 20)->default('active');
            $table->json('response_raw')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_seps');
    }
};
