<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informed_consents', function (Blueprint $table) {
            $table->string('patient_signature_hash', 64)->nullable()->after('patient_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('informed_consents', function (Blueprint $table) {
            $table->dropColumn('patient_signature_hash');
        });
    }
};
