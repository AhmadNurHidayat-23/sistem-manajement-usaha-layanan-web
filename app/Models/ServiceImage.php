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

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path);
        }

        if ($this->image_path && file_exists(public_path('storage/'.$this->image_path))) {
            return asset('storage/'.$this->image_path);
        }

        return $this->image_path ? Storage::url($this->image_path) : asset('images/service-placeholder.svg');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
