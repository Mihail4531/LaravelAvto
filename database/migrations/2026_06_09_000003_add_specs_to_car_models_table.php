<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Технические характеристики модели авто (необязательные).
     * Все поля nullable — у старых записей просто не заполнены.
     */
    public function up(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->string('fuel_type')->nullable()->after('name');        // бензин / дизель / гибрид / электро / газ
            $table->decimal('engine_volume', 3, 1)->nullable()->after('fuel_type'); // объём двигателя, л
            $table->unsignedSmallInteger('power')->nullable()->after('engine_volume'); // мощность, л.с.
            $table->string('transmission')->nullable()->after('power');    // МКПП / АКПП / робот / вариатор
            $table->string('body_type')->nullable()->after('transmission'); // тип кузова
        });
    }

    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn(['fuel_type', 'engine_volume', 'power', 'transmission', 'body_type']);
        });
    }
};
