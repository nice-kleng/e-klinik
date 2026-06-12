<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (!Schema::hasColumn('queues', 'registration_id')) {
                $table->foreignId('registration_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('queues', 'queue_sequence')) {
                $table->integer('queue_sequence')->default(0);
            }
            if (!Schema::hasColumn('queues', 'source')) {
                $table->enum('source', ['walk_in', 'mjkn'])->default('walk_in');
            }
            if (!Schema::hasColumn('queues', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable();
            }
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropColumn([
                'patient_id',
                'doctor_id',
                'estimated_wait_time',
                'called_at',
                'completed_at',
                'service_type',
                'bpjs_antrian_id',
                'bpjs_sep_id',
                'notes',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('estimated_wait_time')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('service_type', ['umum', 'gigi', 'kia', 'lansia', 'lainnya'])->default('umum');
            $table->string('bpjs_antrian_id', 50)->nullable();
            $table->string('bpjs_sep_id', 50)->nullable();
            $table->text('notes')->nullable();

            $table->dropColumn(['registration_id', 'queue_sequence', 'source', 'confirmed_at']);
        });
    }
};
