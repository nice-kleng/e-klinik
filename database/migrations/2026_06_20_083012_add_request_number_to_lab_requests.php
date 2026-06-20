<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->string('request_number', 30)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropUnique(['request_number']);
            $table->dropColumn('request_number');
        });
    }
};
