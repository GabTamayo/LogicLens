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

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityLink(): HasMany
    {
        return $this->hasMany(ActivityLink::class);
    }
}
