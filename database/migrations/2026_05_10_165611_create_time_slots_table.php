<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('available')->default(true);
            $table->timestamps();
            $table->index(['branch_id', 'starts_at', 'available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
