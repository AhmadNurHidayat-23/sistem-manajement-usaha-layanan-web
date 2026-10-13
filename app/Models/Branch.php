<?php

namespace App\Models;

use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uuid', 'name', 'address', 'phone', 'opening_time', 'closing_time', 'service_mode', 'operating_days', 'max_slot_capacity', 'is_active'])]
class Branch extends Model
{
    use HasFactory, HasPublicUuid;

    /**
     * Hari operasional yang tersedia (label Indonesia → value DB).
     *
     * @var array<string, string>
     */
    public const DAYS_OF_WEEK = [
        'Senin' => 'senin',
        'Selasa' => 'selasa',
        'Rabu' => 'rabu',
        'Kamis' => 'kamis',
        'Jumat' => 'jumat',
        'Sabtu' => 'sabtu',
        'Minggu' => 'minggu',
    ];

    protected function casts(): array
    {
        return [
            'max_slot_capacity' => 'integer',
            'is_active' => 'boolean',
            'operating_days' => 'array',
        ];
    }

    /**
     * Nama usaha final bersih sesuai 3 unit usaha SIMAUL:
     * - Barbershop & Studio
     * - Laundry & Shoe Care
     * - Auto Care & Detailing
     */
    public static function cleanBusinessName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        // Hapus awalan 'SIMAUL'
        $clean = preg_replace('/^SIMAUL\s+/i', '', (string) $name);

        // Hapus akhiran '(Cabang ...)' atau '(Pusat/Selatan/Barat)'
        $clean = preg_replace('/\s*\((?:Cabang\s+)?[^)]+\)$/i', '', (string) $clean);

        // Hapus label Pusat, Selatan, Barat di akhir jika ada tanda hubung atau imbuhan
        $clean = preg_replace('/\s*(?:-|–)?\s*(?:Cabang\s+)?(?:Pusat|Selatan|Barat)$/i', '', (string) $clean);

        $trimmed = trim((string) $clean);

        // Jika hanya label cabang tanpa nama usaha
        if (preg_match('/^(?:Cabang\s+)?Pusat$/i', $trimmed)) {
            return 'Barbershop & Studio';
        }
        if (preg_match('/^(?:Cabang\s+)?Selatan$/i', $trimmed)) {
            return 'Laundry & Shoe Care';
        }
        if (preg_match('/^(?:Cabang\s+)?Barat$/i', $trimmed)) {
            return 'Auto Care & Detailing';
        }

        // Khusus 3 unit usaha resmi SIMAUL, pastikan nama baku:
        if (preg_match('/^Barbershop(?:\s*&\s*Studio)?$/i', $trimmed)) {
            return 'Barbershop & Studio';
        }

        if (preg_match('/^(?:SIMAUL\s+)?Laundry(?:\s*&\s*Shoe(?:\s*Care)?)?$/i', $trimmed)) {
            return 'Laundry & Shoe Care';
        }

        if (preg_match('/^(?:SIMAUL\s+)?Auto\s*Care(?:\s*&\s*Detailing)?$/i', $trimmed)) {
            return 'Auto Care & Detailing';
        }

        return $trimmed !== '' ? $trimmed : trim((string) $name);
    }

    /**
     * Nama cabang/usaha bersih untuk antarmuka.
     */
    public function getDisplayNameAttribute(): string
    {
        return self::cleanBusinessName($this->name);
    }

    /**
     * Label hari operasional yang mudah dibaca.
     */
    public function getOperatingDaysLabelAttribute(): string
    {
        if (empty($this->operating_days)) {
            return 'Setiap Hari';
        }

        $daysMap = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            'minggu' => 'Minggu',
        ];

        $days = array_map(fn ($d) => strtolower($d), $this->operating_days);
        if (count($days) === 7) {
            return 'Setiap Hari';
        }

        if (count($days) === 6 && ! in_array('minggu', $days, true)) {
            return 'Senin - Sabtu (Minggu Libur)';
        }

        if (count($days) === 6 && ! in_array('senin', $days, true)) {
            return 'Selasa - Minggu (Senin Libur)';
        }

        return implode(', ', array_map(fn ($d) => $daysMap[$d] ?? ucfirst($d), $days));
    }

    /**
     * Label jam operasional yang diformat.
     */
    public function getOperatingHoursLabelAttribute(): string
    {
        if (! $this->opening_time && ! $this->closing_time) {
            return 'Jam operasional fleksibel';
        }

        $open = $this->opening_time ? substr($this->opening_time, 0, 5) : '00:00';
        $close = $this->closing_time ? substr($this->closing_time, 0, 5) : '23:59';

        return "{$open} - {$close} WIB";
    }

    /**
     * Cek apakah cabang buka pada hari tertentu.
     * Null operating_days berarti buka setiap hari (backward compatible).
     */
    public function isOpenOnDay(string $dayName): bool
    {
        if ($this->operating_days === null) {
            return true;
        }

        return in_array(strtolower($dayName), array_map('strtolower', $this->operating_days), true);
    }

    /**
     * Cek apakah cabang buka pada tanggal tertentu.
     */
    public function isOpenOnDate(string $date): bool
    {
        $dayIndex = (int) date('N', strtotime($date)); // 1=Monday, 7=Sunday
        $dayNames = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];

        return $this->isOpenOnDay($dayNames[$dayIndex]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches')->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
