<?php

namespace App\Models;

use App\Enums\LoyaltyTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['membership_id', 'transaction_id', 'type', 'points', 'description'])]
class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => LoyaltyTransactionType::class,
            'points' => 'integer',
        ];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
