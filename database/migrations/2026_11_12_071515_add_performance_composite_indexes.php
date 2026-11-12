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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('booking_date', 'idx_bookings_booking_date');
            $table->index('status', 'idx_bookings_status');
            $table->index(['branch_id', 'booking_date'], 'idx_bookings_branch_date');
            $table->index(['employee_id', 'booking_date'], 'idx_bookings_employee_date');
            $table->index(['user_id', 'created_at'], 'idx_bookings_user_created');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'idx_orders_status');
            $table->index(['branch_id', 'status'], 'idx_orders_branch_status');
            $table->index(['employee_id', 'status'], 'idx_orders_employee_status');
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
        });

        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->index('shift_date', 'idx_shifts_shift_date');
            $table->index('status', 'idx_shifts_status');
            $table->index(['branch_id', 'shift_date'], 'idx_shifts_branch_date');
            $table->index(['employee_id', 'shift_date'], 'idx_shifts_employee_date');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['status', 'transaction_date'], 'idx_transactions_status_date');
            $table->index(['branch_id', 'status', 'transaction_date'], 'idx_transactions_branch_status_date');
        });

        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->index(['employee_id', 'status'], 'idx_commissions_employee_status');
            $table->index(['status', 'created_at'], 'idx_commissions_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_booking_date');
            $table->dropIndex('idx_bookings_status');
            $table->dropIndex('idx_bookings_branch_date');
            $table->dropIndex('idx_bookings_employee_date');
            $table->dropIndex('idx_bookings_user_created');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_branch_status');
            $table->dropIndex('idx_orders_employee_status');
            $table->dropIndex('idx_orders_user_created');
        });

        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->dropIndex('idx_shifts_shift_date');
            $table->dropIndex('idx_shifts_status');
            $table->dropIndex('idx_shifts_branch_date');
            $table->dropIndex('idx_shifts_employee_date');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_status_date');
            $table->dropIndex('idx_transactions_branch_status_date');
        });

        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->dropIndex('idx_commissions_employee_status');
            $table->dropIndex('idx_commissions_status_created');
        });
    }
};
