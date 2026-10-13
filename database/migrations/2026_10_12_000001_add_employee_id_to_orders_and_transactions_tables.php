<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('service_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('cashier_id')->constrained('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};
