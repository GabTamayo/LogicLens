<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'activity_link_id',
        'user_id',
        'code_content',
        'language',
        'draft_code',
        'draft_stdin',
        'draft_saved_at',
    ];

    protected $casts = [
        'draft_saved_at' => 'datetime',
    ];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detection(): HasMany
    {
        return $this->hasMany(Detection::class);
    }

    public function detectionA(): HasMany
    {
        return $this->hasMany(Detection::class, 'submission_a_id');
    }

    public function detectionB(): HasMany
    {
        return $this->hasMany(Detection::class, 'submission_b_id');
    }

    public function scopeUndetected($query)
    {
        return $query->whereDoesntHave('detectionA')->whereDoesntHave('detectionB');
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'code_content', 'language', 'created_at');
    }

    public function scopeFilterByStudent($query, ?string $name = null)
    {
        return $query->when($name, fn ($q) => $q->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$name}%")));
    }

    public function activityLanguage(): ?string
    {
        return $this->activityLink?->activity?->language;
    }
}
