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
            $table->decimal('actual_weight', 12, 2)->nullable()->after('estimated_quantity');
            $table->decimal('total_amount', 12, 2)->nullable()->after('actual_weight');
            $table->timestamp('weighed_at')->nullable()->after('total_amount');
            $table->foreignId('weighed_by')->nullable()->after('weighed_at')->constrained('users')->nullOnDelete();
            $table->string('delivery_method', 50)->nullable()->after('pickup_option');
            $table->timestamp('delivery_selected_at')->nullable()->after('delivery_method');
            $table->string('handover_status', 50)->default('pending')->after('delivery_selected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['weighed_by']);
            $table->dropColumn([
                'actual_weight',
                'total_amount',
                'weighed_at',
                'weighed_by',
                'delivery_method',
                'delivery_selected_at',
                'handover_status',
            ]);
        });
    }
};
