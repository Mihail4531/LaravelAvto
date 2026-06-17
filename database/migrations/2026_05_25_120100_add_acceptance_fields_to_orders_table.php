<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Состояние автомобиля при приёмке
            $table->text('damages_on_acceptance')->nullable()->after('current_mileage');
            $table->string('equipment')->nullable()->after('damages_on_acceptance');
            $table->string('fuel_level', 20)->nullable()->after('equipment');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['damages_on_acceptance', 'equipment', 'fuel_level']);
        });
    }
};
