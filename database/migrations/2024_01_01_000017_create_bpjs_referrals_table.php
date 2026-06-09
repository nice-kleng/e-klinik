<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('no_kunjungan', 50)->nullable();
            $table->string('no_sep', 50)->nullable();
            $table->date('tgl_kunjungan')->nullable();
            $table->string('ppk_dirujuk')->nullable();
            $table->string('diagnose')->nullable();
            $table->string('tipe_referensi')->nullable();
            $table->string('status')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index('no_sep');
            $table->index('no_kunjungan');
            $table->index('ppk_dirujuk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_referrals');
    }
};
