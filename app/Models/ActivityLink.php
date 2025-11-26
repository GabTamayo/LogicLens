<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ActivityLink extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLinkFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'token',
        'is_open',
        'expires_at',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'activity_id', 'name', 'token', 'is_open', 'expires_at', 'created_at');
    }

    public function scopeWithSubmissions($query)
    {
        return $query->with(['submissions' => fn($q) => $q->latest()]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    public function setExpiresAtAttribute($value)
    {
        $this->attributes['expires_at'] = $value
            ? Carbon::parse($value)->timezone('Asia/Manila')
            : null;
    }

    public function getExpiresAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Manila') : null;
    }

    protected static function booted()
    {
        static::deleting(function ($activityLink) {
            foreach ($activityLink->submissions as $submission) {
                if ($submission->file_path && Storage::disk(env('FILESYSTEM_DISK'))->exists($submission->file_path)) {
                    Storage::disk(env('FILESYSTEM_DISK'))->delete($submission->file_path);
                }
            }
        });
    }
}
