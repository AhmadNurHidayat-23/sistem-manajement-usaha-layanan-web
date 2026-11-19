<?php

namespace App\Models;

use App\Enums\StatusShiftKaryawan;
use Carbon\Carbon;
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

    /**
     * Waktu mulai lengkap shift dalam timezone Asia/Jakarta.
     */
    public function getStartDateTimeAttribute(): Carbon
    {
        $dateStr = $this->shift_date instanceof \DateTimeInterface
            ? $this->shift_date->format('Y-m-d')
            : substr((string) $this->shift_date, 0, 10);

        $startTime = $this->start_time ? substr((string) $this->start_time, 0, 8) : '00:00:00';

        return Carbon::parse("{$dateStr} {$startTime}", 'Asia/Jakarta');
    }

    /**
     * Waktu selesai lengkap shift dalam timezone Asia/Jakarta,
     * secara otomatis menangani shift lintas tengah malam (+1 hari jika end <= start).
     */
    public function getEndDateTimeAttribute(): Carbon
    {
        $dateStr = $this->shift_date instanceof \DateTimeInterface
            ? $this->shift_date->format('Y-m-d')
            : substr((string) $this->shift_date, 0, 10);

        $endTime = $this->end_time ? substr((string) $this->end_time, 0, 8) : '00:00:00';
        $startDateTime = $this->start_date_time;
        $endDateTime = Carbon::parse("{$dateStr} {$endTime}", 'Asia/Jakarta');

        if ($endDateTime->lte($startDateTime)) {
            $endDateTime->addDay();
        }

        return $endDateTime;
    }

    /**
     * Periksa apakah jadwal shift telah melewati jam selesai.
     */
    public function isEnded(?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');

        return $now->greaterThan($this->end_date_time);
    }

    /**
     * Periksa apakah jadwal shift masih di masa mendatang (belum dimulai).
     */
    public function isUpcoming(?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');

        return $now->lessThan($this->start_date_time);
    }

    /**
     * Status efektif untuk tampilan:
     * - Jika status masih scheduled dan jam selesai telah lewat -> StatusShiftKaryawan::Ended (Berakhir).
     * - Jika status hadir/terlambat/absen/libur -> tetap pertahankan status kehadiran riil.
     * - Jika belum lewat -> tetap scheduled (Terjadwal).
     */
    public function getEffectiveStatusAttribute(): StatusShiftKaryawan
    {
        $currentStatus = $this->status instanceof StatusShiftKaryawan
            ? $this->status
            : (StatusShiftKaryawan::tryFrom((string) $this->status) ?? StatusShiftKaryawan::Scheduled);

        if ($currentStatus === StatusShiftKaryawan::Scheduled && $this->isEnded()) {
            return StatusShiftKaryawan::Ended;
        }

        return $currentStatus;
    }
}
