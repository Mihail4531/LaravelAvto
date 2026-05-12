<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('cars', function (Blueprint $table) {
        // Удаляем уникальный индекс, созданный для license_plate
        $table->dropUnique(['license_plate']);
        // Теперь можно удалять столбцы
        $table->dropColumn(['license_plate', 'brand_text', 'model_text']);
    });
}

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('license_plate')->nullable()->unique()->after('client_id');
            $table->string('brand_text')->nullable()->after('car_model_id');
            $table->string('model_text')->nullable()->after('brand_text');
        });
    }
};
