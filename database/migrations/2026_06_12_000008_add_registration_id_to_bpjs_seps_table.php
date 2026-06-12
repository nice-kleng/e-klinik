<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpjs_seps', function (Blueprint $table) {
            if (!Schema::hasColumn('bpjs_seps', 'registration_id')) {
                $table->foreignId('registration_id')->nullable()->after('queue_id')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bpjs_seps', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->dropColumn('registration_id');
        });
    }
};
