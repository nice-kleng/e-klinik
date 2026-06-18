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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('province_kd', 10)->nullable()->after('province');
            $table->string('city_kd', 10)->nullable()->after('city');
            $table->string('district_kd', 10)->nullable()->after('district');
            $table->string('village_kd', 10)->nullable()->after('village');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['province_kd', 'city_kd', 'district_kd', 'village_kd']);
        });
    }
};
