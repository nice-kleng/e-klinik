<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->text('past_history')->nullable()->after('anamnesis');
            $table->text('medication_history')->nullable()->after('past_history');
            $table->text('differential_diagnosis')->nullable()->after('assessment');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['past_history', 'medication_history', 'differential_diagnosis']);
        });
    }
};
