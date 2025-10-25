<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'title',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLinks(): HasMany
    {
        return $this->hasMany(ActivityLink::class);
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'title', 'created_at');
    }

    protected static function booted()
    {
        static::deleting(function ($activity) {
            $activity->load('activityLinks');
            foreach ($activity->activityLinks as $link) {
                $link->delete();
            }
        });
    }
}
