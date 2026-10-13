<?php

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discount_type')->default(DiscountType::Fixed->value);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->integer('quota')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default(PromotionStatus::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
