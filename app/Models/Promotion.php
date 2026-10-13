<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uuid', 'branch_id', 'code', 'name', 'discount_type', 'discount_value', 'quota', 'start_date', 'end_date', 'status'])]
class Promotion extends Model
{
    use HasFactory, HasPublicUuid;

    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'quota' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => PromotionStatus::class,
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }
}
