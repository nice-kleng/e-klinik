<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->text('final_diagnosis')->nullable();
            $table->enum('discharge_status', ['sembuh', 'dirujuk', 'pulang_paksa', 'meninggal', 'lainnya'])->default('sembuh');
            $table->text('follow_up_plan')->nullable();
            $table->text('referral_notes')->nullable();
            $table->string('referral_to')->nullable();
            $table->unsignedTinyInteger('sick_leave_days')->nullable();
            $table->date('sick_leave_from')->nullable();
            $table->date('sick_leave_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_summaries');
    }
};
