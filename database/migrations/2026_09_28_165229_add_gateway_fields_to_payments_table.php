<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds payment gateway specific columns to existing payments table.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Payment channel within a method, e.g. method=qris, channel=gopay_qris
            $table->string('channel', 50)->nullable()->after('payment_method');

            // Gateway's own reference / external order ID
            $table->string('gateway_reference', 100)->nullable()->after('reference_number');

            // Payload mentah dari gateway
            $table->json('gateway_payload')->nullable()->after('gateway_reference');

            // Waktu kedaluwarsa pembayaran
            $table->timestamp('expired_at')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['channel', 'gateway_reference', 'gateway_payload', 'expired_at']);
        });
    }
};
