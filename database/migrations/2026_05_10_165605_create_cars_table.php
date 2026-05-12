<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_brand_id')->nullable()->constrained('car_brands')->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained('car_models')->nullOnDelete();
            $table->string('brand_text')->nullable(); // свободное поле, если бренда нет в справочнике
            $table->string('model_text')->nullable();
            $table->integer('year')->nullable();
            $table->string('license_plate')->unique();
            $table->string('vin', 17)->nullable()->unique();
            $table->integer('mileage')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
