<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained();
            $table->foreignId('executor_id')->constrained('users'); // мастер
            $table->decimal('quantity', 5, 2)->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('sum', 12, 2);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_service');
    }
};
