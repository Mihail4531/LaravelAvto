<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_brand_id')->nullable()->constrained('car_brands')->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained('car_models')->nullOnDelete();
            $table->string('car_brand_text')->nullable();
            $table->string('car_model_text')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();
            $table->text('problem_description')->nullable();
            $table->enum('status', [
                'new', 'confirmed', 'rejected', 'converted', 'cancelled'
            ])->default('new');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('processed_at')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->string('reject_reason')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('client_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
