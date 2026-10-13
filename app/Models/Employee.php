<?php

namespace App\Models;

use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uuid', 'user_id', 'branch_id', 'name', 'phone', 'avatar', 'status'])]
class Employee extends Model
{
    use HasFactory, HasPublicUuid;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }

        if ($this->user && $this->user->avatar_url) {
            return $this->user->avatar_url;
        }

        return null;
    }
}
