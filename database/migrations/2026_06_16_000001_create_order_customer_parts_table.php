<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запчасти/материалы, которые привёз сам клиент (давальческие).
     * Не со склада, денег за них не берём — только фиксируем, что установили.
     */
    public function up(): void
    {
        Schema::create('order_customer_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('name');                         // наименование детали клиента
            $table->decimal('quantity', 10, 2)->default(1); // допускаем дробное (масло в литрах и т.п.)
            $table->string('unit')->default('шт');
            $table->string('note')->nullable();             // примечание (марка, состояние и т.п.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_customer_parts');
    }
};
