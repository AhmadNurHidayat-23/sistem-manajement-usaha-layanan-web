<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasPublicUuid
{
    /**
     * Otomatis set UUID v4 saat model baru dibuat jika belum ada.
     */
    public static function bootHasPublicUuid(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Gunakan kolom UUID untuk route model binding publik.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Dapatkan nilai route key dengan fallback ke ID numerik jika UUID belum terisi.
     */
    public function getRouteKey(): mixed
    {
        return $this->getAttribute($this->getRouteKeyName()) ?: $this->getKey();
    }

    /**
     * Resolusi route binding dengan fallback kompatibilitas ke ID numerik.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        $query = $this->where($field, $value);

        if (is_numeric($value)) {
            $query->orWhere($this->getKeyName(), (int) $value);
        }

        return $query->first() ?? abort(404);
    }
}
