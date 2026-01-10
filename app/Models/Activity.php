<?php

namespace App\Models;

use App\Enums\ProgrammingLanguage;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stevebauman\Purify\Casts\PurifyHtmlOnSet;

class Activity extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'title',
        'language',
        'content',
        'time_limit',
    ];

    protected $casts = [
        'content' => PurifyHtmlOnSet::class,
    ];

    protected $appends = ['language_text'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLinks(): HasMany
    {
        return $this->hasMany(ActivityLink::class);
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class)->orderBy('order');
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'title', 'language', 'content', 'created_at');
    }

    protected static function booted()
    {
        static::deleting(function ($activity) {
            foreach ($activity->activityLinks as $link) {
                $link->delete();
            }

            $activity->testCases()->delete();
        });
    }

    public function getLanguageTextAttribute(): ?string
    {
        return ProgrammingLanguage::response($this->language);
    }

    public function hasTimeLimit(): bool
    {
        return $this->time_limit !== null;
    }

    public function getTimeLimitInMinutes(): ?int
    {
        return $this->time_limit;
    }
}
