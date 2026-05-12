<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_part', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->decimal('quantity', 8, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('sum', 12, 2);
            $table->boolean('is_issued')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_part');
    }
};
