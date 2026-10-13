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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('unit')->default('pcs');
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(5);
            $table->decimal('cost_per_unit', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'current_stock']);
        });

        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // in, out, adjustment
            $table->decimal('quantity', 10, 2);
            $table->decimal('stock_before', 10, 2);
            $table->decimal('stock_after', 10, 2);
            $table->string('reference_type')->nullable(); // manual, restock, usage, damaged, initial
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_mutations');
        Schema::dropIfExists('inventory_items');
    }
};
