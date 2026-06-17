<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Характеристики авто переносятся с модели (справочник) на конкретный
     * автомобиль клиента — там им и место (двигатель/КПП у каждой машины свои).
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('fuel_type')->nullable()->after('color');
            $table->decimal('engine_volume', 3, 1)->nullable()->after('fuel_type');
            $table->unsignedSmallInteger('power')->nullable()->after('engine_volume');
            $table->string('transmission')->nullable()->after('power');
            $table->string('body_type')->nullable()->after('transmission');
        });

        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn(['fuel_type', 'engine_volume', 'power', 'transmission', 'body_type']);
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['fuel_type', 'engine_volume', 'power', 'transmission', 'body_type']);
        });

        Schema::table('car_models', function (Blueprint $table) {
            $table->string('fuel_type')->nullable()->after('name');
            $table->decimal('engine_volume', 3, 1)->nullable()->after('fuel_type');
            $table->unsignedSmallInteger('power')->nullable()->after('engine_volume');
            $table->string('transmission')->nullable()->after('power');
            $table->string('body_type')->nullable()->after('transmission');
        });
    }
};
