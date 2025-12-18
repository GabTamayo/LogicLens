<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    /** @use HasFactory<\Database\Factories\DetectionFactory> */
    use HasFactory, HasUuid;

    public $incrementing = false;

    protected $fillable = [
        'activity_link_id',
        'submission_a_id',
        'submission_b_id',
        'seq_score',
        'struct_score',
        'avg_score',
        'line_matches',
        'flagged',
    ];

    protected $casts = [
        'seq_score' => 'float',
        'struct_score' => 'float',
        'avg_score' => 'float',
        'line_matches' => 'array',
        'flagged' => 'boolean',
    ];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function submissionA(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_a_id')
            ->select(['id', 'user_id', 'code_content', 'language']);
    }

    public function submissionB(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_b_id')
            ->select(['id', 'user_id', 'code_content', 'language']);
    }

    // Code content is now directly accessible from submission models
    public function getCodeContentA()
    {
        return $this->submissionA->code_content;
    }

    public function getCodeContentB()
    {
        return $this->submissionB->code_content;
    }

    public function flag()
    {
        $this->update(['flagged' => true]);
    }

    public function scopeForLink($query, $linkId)
    {
        return $query->where('activity_link_id', $linkId);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['student_name_a'] ?? null, function ($q, $name) {
                $q->whereHas('submissionA.user', fn ($s) => $s->where('name', 'like', "%{$name}%"));
            })
            ->when($filters['student_name_b'] ?? null, function ($q, $name) {
                $q->whereHas('submissionB.user', fn ($s) => $s->where('name', 'like', "%{$name}%"));
            });
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select(
            'id',
            'activity_link_id',
            'submission_a_id',
            'submission_b_id',
            'seq_score',
            'struct_score',
            'avg_score',
            'line_matches',
            'flagged',
        );
    }
}
