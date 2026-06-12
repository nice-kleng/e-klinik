<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('registration_date');
            $table->enum('source', ['walk_in', 'mjkn', 'rujukan'])->default('walk_in');
            $table->enum('service_status', [
                'registered', 'in_consultation', 'lab', 'pharmacy', 'cashier', 'completed', 'cancelled',
            ])->default('registered');
            $table->string('bpjs_antrian_id', 50)->nullable()->index();
            $table->string('no_sep', 50)->nullable();
            $table->string('age_text', 50)->nullable();
            $table->tinyInteger('age_years')->default(0);
            $table->tinyInteger('age_months')->default(0);
            $table->tinyInteger('age_days')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['registration_date', 'polyclinic_id']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
