<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('position_id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->date('hire_date')->nullable()->after('phone');
            $table->boolean('active')->default(true)->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['position_id', 'branch_id', 'phone', 'hire_date', 'active']);
        });
    }
};
