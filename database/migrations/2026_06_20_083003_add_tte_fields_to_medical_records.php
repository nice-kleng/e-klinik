<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->foreignId('signed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('signed_at')->nullable();

            $table->string('signature_hash', 64)
                ->nullable()
                ->unique()
                ->comment('SHA-256 hash dari konten RME');

            $table->boolean('is_tte_verified')
                ->default(false)
                ->comment('Status verifikasi TTE');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by');
            $table->dropColumn(['signed_at', 'signature_hash', 'is_tte_verified']);
        });
    }
};
