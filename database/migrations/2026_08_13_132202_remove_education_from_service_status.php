<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN service_status ENUM('registered','triage','in_consultation','lab','pharmacy','cashier','completed','cancelled') NOT NULL DEFAULT 'registered'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN service_status ENUM('registered','triage','in_consultation','lab','pharmacy','cashier','education','resume','completed','cancelled') NOT NULL DEFAULT 'registered'");
    }
};
