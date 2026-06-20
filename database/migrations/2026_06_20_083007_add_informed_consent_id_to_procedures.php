<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->foreignId('informed_consent_id')
                ->nullable()
                ->after('informed_consent_file')
                ->constrained('informed_consents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('informed_consent_id');
        });
    }
};
