<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Удаляем поля автомобиля
            $table->dropColumn([
                'car_brand_text',
                'car_model_text',
                'license_plate',

            ]);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('car_brand_text')->nullable();
            $table->string('car_model_text')->nullable();
            $table->string('license_plate')->nullable();
       
        });
    }
};
