<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityLink extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
