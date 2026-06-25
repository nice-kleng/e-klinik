<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->boolean('is_compound')->default(false)->after('subtotal');
            $table->string('compound_name', 100)->nullable()->after('is_compound');
            $table->integer('total_packets')->nullable()->after('compound_name');
            $table->text('instruction')->nullable()->after('total_packets');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['is_compound', 'compound_name', 'total_packets', 'instruction']);
        });
    }
};
