<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('receptionist', 'web');

        Artisan::call('cache:clear');
    }

    public function down(): void
    {
        Role::where('name', 'receptionist')->delete();
    }
};
