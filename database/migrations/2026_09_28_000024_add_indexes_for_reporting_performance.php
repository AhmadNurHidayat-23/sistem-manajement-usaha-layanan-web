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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('transaction_date');
            $table->index('status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at');
            $table->index('status');
            $table->index('payment_method');
        });

        Schema::table('promotion_usages', function (Blueprint $table) {
            $table->index('used_at');
        });

        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['transaction_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_method']);
        });

        Schema::table('promotion_usages', function (Blueprint $table) {
            $table->dropIndex(['used_at']);
        });

        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
