<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Номер кузова и номер двигателя конкретного авто. Нужны для заказ-наряда:
     * на старых машинах (часто японских) кузов и двигатель имеют собственные
     * номера, не совпадающие с VIN, и их фиксируют в документе на работы.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('body_number')->nullable()->after('vin');
            $table->string('engine_number')->nullable()->after('body_number');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['body_number', 'engine_number']);
        });
    }
};
