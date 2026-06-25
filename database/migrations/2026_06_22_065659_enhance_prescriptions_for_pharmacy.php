<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_per_packet', 10, 2);
            $table->string('unit', 20)->default('mg');
            $table->timestamps();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('dispensed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->boolean('is_compound')->default(false)->after('subtotal');
            $table->string('compound_name', 100)->nullable()->after('is_compound');
            $table->integer('total_packets')->nullable()->after('compound_name');
            $table->text('instruction')->nullable()->after('total_packets');
        });

        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active','dispensed','cancelled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE prescriptions MODIFY COLUMN status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['dispensed_by', 'dispensed_at', 'cancellation_reason']);
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['is_compound', 'compound_name', 'total_packets', 'instruction']);
        });

        Schema::dropIfExists('prescription_item_ingredients');
    }
};
