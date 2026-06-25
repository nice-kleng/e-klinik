<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_item_ingredients', function (Blueprint $table) {
            $table->decimal('calculated_qty', 10, 2)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_item_ingredients', function (Blueprint $table) {
            $table->dropColumn('calculated_qty');
        });
    }
};
