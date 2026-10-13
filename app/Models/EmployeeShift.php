<?php

namespace App\Models;

use App\Enums\StatusShiftKaryawan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'branch_id', 'shift_date', 'start_time', 'end_time', 'status'])]
class EmployeeShift extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'status' => StatusShiftKaryawan::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
