<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Применяемость запчастей: универсальные (подходят всем) и связь
     * «запчасть ↔ модели авто» для конкретных деталей.
     */
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->boolean('is_universal')->default(false)->after('active');
        });

        Schema::create('car_model_part', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_model_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['part_id', 'car_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_model_part');
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('is_universal');
        });
    }
};
