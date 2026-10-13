<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'transaction_id', 'service_id', 'commission_type', 'commission_value', 'commission_amount', 'status'])]
class EmployeeCommission extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'commission_type' => CommissionType::class,
            'commission_value' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'status' => CommissionStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
