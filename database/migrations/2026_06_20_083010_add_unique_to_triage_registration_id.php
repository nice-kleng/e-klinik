<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('triage', function (Blueprint $table) {
            $table->unique('registration_id', 'triage_registration_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('triage', function (Blueprint $table) {
            $table->dropUnique('triage_registration_id_unique');
        });
    }
};
