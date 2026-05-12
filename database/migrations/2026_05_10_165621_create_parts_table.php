<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('article')->unique();
            $table->string('name');
            $table->string('unit', 20)->default('шт');
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->string('location')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
