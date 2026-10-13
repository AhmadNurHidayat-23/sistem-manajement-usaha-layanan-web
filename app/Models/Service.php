<?php

namespace App\Models;

use App\Enums\CommissionType;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['uuid', 'service_category_id', 'branch_id', 'name', 'description', 'image_path', 'price', 'unit', 'estimated_duration', 'service_type', 'is_active', 'commission_type', 'commission_value'])]
class Service extends Model
{
    use HasFactory, HasPublicUuid;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'estimated_duration' => 'integer',
            'is_active' => 'boolean',
            'commission_type' => CommissionType::class,
            'commission_value' => 'decimal:2',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }

        return null;
    }

    public function isWeightBased(): bool
    {
        $unit = strtolower(trim((string) ($this->unit ?? '')));
        if ($unit === 'kg' || $unit === 'kilogram') {
            return true;
        }

        $name = strtolower($this->name ?? '');
        if (str_contains($name, 'kiloan') || str_contains($name, '/kg')) {
            return true;
        }

        $categoryName = strtolower($this->category?->name ?? '');
        if (str_contains($categoryName, 'kiloan') || str_contains($categoryName, 'kilo')) {
            return true;
        }

        return false;
    }

    /**
     * Format estimasi durasi dalam jam atau hari untuk tampilan customer.
     *
     * Aturan:
     * - durasi kurang dari 60 menit dibulatkan menjadi ±1 jam
     * - durasi di bawah 1 hari (1440 menit) tampil dalam JAM
     * - durasi 1 hari atau lebih tampil dalam HARI
     * - gunakan format Bahasa Indonesia (misal: ±1,5 jam)
     */
    public static function formatEstimatedDuration(?int $minutes): string
    {
        if (empty($minutes) || $minutes <= 0) {
            return '±1 jam';
        }

        // Durasi 1 hari atau lebih (1 hari = 1440 menit)
        if ($minutes >= 1440) {
            $days = round($minutes / 1440, 1);
            $formatted = (floor($days) == $days)
                ? (int) $days
                : str_replace('.', ',', (string) $days);

            return "±{$formatted} hari";
        }

        // Durasi kurang dari 60 menit dibulatkan menjadi ±1 jam
        if ($minutes < 60) {
            return '±1 jam';
        }

        // Durasi di bawah 1 hari tampil dalam JAM
        $hours = round($minutes / 60, 1);
        $formatted = (floor($hours) == $hours)
            ? (int) $hours
            : str_replace('.', ',', (string) $hours);

        return "±{$formatted} jam";
    }

    public function getFormattedDurationAttribute(): string
    {
        return self::formatEstimatedDuration($this->estimated_duration);
    }

    public function hasCommissionConfigured(): bool
    {
        return $this->commission_type !== null && $this->commission_value !== null;
    }

    public function getFormattedCommissionAttribute(): string
    {
        if (! $this->hasCommissionConfigured()) {
            return 'Belum diatur';
        }

        $type = $this->commission_type instanceof CommissionType
            ? $this->commission_type->value
            : (string) $this->commission_type;

        if ($type === CommissionType::Percentage->value) {
            $val = (float) $this->commission_value;
            $formattedVal = (floor($val) == $val) ? (int) $val : str_replace('.', ',', (string) $val);

            return "{$formattedVal}%";
        }

        return 'Rp '.number_format((float) $this->commission_value, 0, ',', '.');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ulasans(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function employeeCommissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }
}
