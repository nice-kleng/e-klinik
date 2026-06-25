<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN service_status ENUM('registered','triage','in_consultation','pharmacy','cashier','education','completed','cancelled') NOT NULL DEFAULT 'registered'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN service_status ENUM('registered','triage','in_consultation','education','completed','cancelled') NOT NULL DEFAULT 'registered'");
    }
};
