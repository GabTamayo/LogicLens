<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        return $this->belongsTo(Submission::class, 'submission_a_id')->select(['id', 'student_name', 'student_no', 'student_email', 'file_path', 'language']);
    }

    public function submissionB(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_b_id')->select(['id', 'student_name', 'student_no', 'student_email', 'file_path', 'language']);
    }

    public function getFileContentA()
    {
        return Storage::disk('public')->get($this->submissionA->file_path);
    }

    public function getFileContentB()
    {
        return Storage::disk('public')->get($this->submissionB->file_path);
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
                $q->whereHas('submissionA', fn($s) => $s->where('student_name', 'like', "%{$name}%"));
            })
            ->when($filters['student_name_b'] ?? null, function ($q, $name) {
                $q->whereHas('submissionB', fn($s) => $s->where('student_name', 'like', "%{$name}%"));
            });
        //->when($filters['min_score'] ?? null, fn($q, $score) => $q->where('similarity_score', '>=', (float) $score));
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
            'created_at'
        );
    }
}
