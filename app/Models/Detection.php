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
        'similarity_score',
    ];

    protected $casts = [
        'similarity_score' => 'float',
    ];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function submissionA(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_a_id');
    }

    public function submissionB(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_b_id');
    }
}
