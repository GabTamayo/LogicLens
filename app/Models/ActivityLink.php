<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLink extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLinkFactory> */
    use HasFactory;

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
