<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->decimal('tuslah', 12, 2)->nullable()->after('subtotal');
            $table->decimal('embalase', 12, 2)->nullable()->after('tuslah');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['tuslah', 'embalase']);
        });
    }
};
