<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['service_id', 'image_path', 'sort_order'])]
class ServiceImage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return self::urlForPath($this->image_path);
    }

    public static function urlForPath(?string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! $path || ! $disk->fileExists($path)) {
            return null;
        }

        return @getimagesize($disk->path($path)) !== false ? Storage::url($path) : null;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
