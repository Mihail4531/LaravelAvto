<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('car_id')->constrained();
            $table->foreignId('receiver_id')->constrained('users'); // приёмщик
            $table->dateTime('planned_finish')->nullable();
            $table->dateTime('actual_finish')->nullable();
            $table->integer('current_mileage')->nullable();
            $table->text('problem_description')->nullable();
            $table->enum('status', [
                'new', 'in_progress', 'completed', 'closed', 'cancelled'
            ])->default('new');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
