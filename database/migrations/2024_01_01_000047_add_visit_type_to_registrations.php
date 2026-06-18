<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('visit_type', ['Baru', 'Lama', 'Kontrol', 'Rujukan'])
                ->default('Baru')
                ->after('source');
            $table->tinyInteger('visit_sequence')
                ->unsigned()
                ->default(0)
                ->after('visit_type');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['visit_type', 'visit_sequence']);
        });
    }
};
