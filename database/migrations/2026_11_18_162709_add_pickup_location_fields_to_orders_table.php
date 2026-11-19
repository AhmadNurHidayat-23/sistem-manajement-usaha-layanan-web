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
            $table->text('pickup_address')->nullable()->after('pickup_option');
            $table->decimal('pickup_latitude', 10, 8)->nullable()->after('pickup_address');
            $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            $table->boolean('different_delivery_location')->default(false)->after('pickup_longitude');
            $table->text('delivery_address')->nullable()->after('different_delivery_location');
            $table->decimal('delivery_latitude', 10, 8)->nullable()->after('delivery_address');
            $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            $table->text('location_notes')->nullable()->after('delivery_longitude');
        });

        if (! Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('address')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_address',
                'pickup_latitude',
                'pickup_longitude',
                'different_delivery_location',
                'delivery_address',
                'delivery_latitude',
                'delivery_longitude',
                'location_notes',
            ]);
        });

        if (Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
