<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_request_items', 'fee')) {
                $table->decimal('fee', 12, 2)->nullable()->after('lab_test_id')->comment('biaya pemeriksaan');
            }
        });

        Schema::table('medical_record_procedures', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_record_procedures', 'fee')) {
                $table->decimal('fee', 12, 2)->nullable()->after('notes')->comment('biaya tindakan');
            }
            if (!Schema::hasColumn('medical_record_procedures', 'informed_consent_id')) {
                $table->foreignId('informed_consent_id')->nullable()->after('informed_consent_file')->constrained('informed_consents')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_request_items', function (Blueprint $table) {
            $table->dropColumn('fee');
        });

        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
};
