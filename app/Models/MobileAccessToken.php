<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'token', 'device_name', 'last_used_at', 'expires_at'])]
class MobileAccessToken extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Buat token baru untuk pengguna mobile.
     *
     * @return array{token: string, record: self}
     */
    public static function generateForUser(User $user, ?string $deviceName = null, ?int $expiresInDays = 30): array
    {
        $plainToken = Str::random(60);
        $hashedToken = hash('sha256', $plainToken);

        $record = self::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'device_name' => $deviceName,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);

        return [
            'token' => $plainToken,
            'record' => $record,
        ];
    }

    /**
     * Cari token aktif berdasarkan plain text token.
     */
    public static function findActiveToken(string $plainToken): ?self
    {
        $hashedToken = hash('sha256', $plainToken);

        $tokenRecord = self::with(['user.role', 'user.branches', 'user.employee'])
            ->where('token', $hashedToken)
            ->first();

        if (! $tokenRecord) {
            return null;
        }

        if ($tokenRecord->expires_at && $tokenRecord->expires_at->isPast()) {
            $tokenRecord->delete();

            return null;
        }

        $tokenRecord->forceFill(['last_used_at' => now()])->save();

        return $tokenRecord;
    }
}
