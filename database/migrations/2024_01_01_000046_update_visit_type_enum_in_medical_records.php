<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->enum('visit_type', ['Baru', 'Lama', 'Kontrol', 'Rujukan'])->default('Baru')->change();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->enum('visit_type', ['rawat_jalan', 'gawat_darurat', 'rawat_inap'])->default('rawat_jalan')->change();
        });
    }
};
