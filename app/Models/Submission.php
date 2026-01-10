<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'started_at',
        'ending_at',
        'submitted_at',
        'score',
    ];

    protected $casts = [
        'draft_saved_at' => 'datetime',
        'started_at' => 'datetime',
        'ending_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    protected $appends = ['total_score'];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function getTotalScoreAttribute(): string
    {
        return number_format(
            $this->activityLink?->activity?->testCases()->sum('score') ?? 0,
            2,
            '.',
            ''
        );
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

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'code_content', 'language', 'score', 'activity_link_id', 'created_at', 'submitted_at');
    }

    public function scopeFilterByStudent($query, ?string $name = null)
    {
        return $query->when($name, fn ($q) => $q->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$name}%")));
    }

    public function activityLanguage(): ?string
    {
        return $this->activityLink?->activity?->language;
    }

    public function startTimer(): void
    {
        if (! $this->started_at && $this->activityLink?->activity?->hasTimeLimit()) {
            $timeLimit = $this->activityLink->activity->time_limit;
            $startedAt = now();
            $endingAt = $startedAt->copy()->addMinutes($timeLimit);

            $this->update([
                'started_at' => $startedAt,
                'ending_at' => $endingAt,
            ]);
        }
    }

    public function hasTimerExpired(): bool
    {
        if (! $this->ending_at) {
            return false;
        }

        return now()->isAfter($this->ending_at);
    }

    public function getTimeRemainingInSeconds(): ?int
    {
        if (! $this->ending_at) {
            return null;
        }

        $remaining = now()->diffInSeconds($this->ending_at, false);

        return max(0, (int) $remaining);
    }

    public function getExpiresAt(): ?Carbon
    {
        return $this->ending_at;
    }
}
