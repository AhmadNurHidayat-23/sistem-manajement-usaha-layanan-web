<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['uuid', 'user_id', 'member_code', 'joined_at', 'status'])]
class Membership extends Model
{
    use HasFactory, HasPublicUuid;

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'status' => MembershipStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loyaltyPoint(): HasOne
    {
        return $this->hasOne(LoyaltyPoint::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
