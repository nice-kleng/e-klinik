<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('performed_at')->nullable();
            $table->text('result')->nullable();
            $table->enum('status', ['ordered', 'in_progress', 'completed', 'cancelled'])->default('ordered');
            $table->boolean('informed_consent')->default(false);
            $table->string('informed_consent_file')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->dropColumn(['operator_id', 'performed_at', 'result', 'status', 'informed_consent', 'informed_consent_file']);
        });
    }
};
