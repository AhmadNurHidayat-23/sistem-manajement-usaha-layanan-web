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
        Schema::table('services', function (Blueprint $table) {
            $table->index(['branch_id', 'is_active'], 'idx_services_branch_active');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'idx_bookings_branch_status');
        });

        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->index(['user_id', 'branch_id', 'status'], 'idx_cashier_shifts_user_branch_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_branch_active');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_branch_status');
        });

        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->dropIndex('idx_cashier_shifts_user_branch_status');
        });
    }
};
