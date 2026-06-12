<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_records', 'registration_id')) {
                $table->foreignId('registration_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }
        });

        if (DB::table('medical_records')->exists() && DB::table('queues')->exists() && DB::table('registrations')->exists()) {
            DB::table('medical_records')
                ->join('queues', 'medical_records.queue_id', '=', 'queues.id')
                ->whereNull('medical_records.registration_id')
                ->whereNotNull('queues.registration_id')
                ->update([
                    'medical_records.registration_id' => DB::raw('queues.registration_id'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->dropColumn('registration_id');
        });
    }
};
