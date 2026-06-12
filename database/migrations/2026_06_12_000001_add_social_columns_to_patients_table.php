<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'education')) {
                $table->enum('education', ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'])
                    ->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('patients', 'mother_name')) {
                $table->string('mother_name', 100)->nullable()->after('marriage_status');
            }
            if (!Schema::hasColumn('patients', 'emergency_contact')) {
                $table->string('emergency_contact', 200)->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('patients', 'allergy')) {
                $table->text('allergy')->nullable()->after('emergency_contact');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['education', 'mother_name', 'emergency_contact', 'allergy']);
        });
    }
};
