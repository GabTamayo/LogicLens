<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamViolation extends Model
{
    protected $fillable = [
        'submission_id',
        'token',
        'violation_type',
        'details',
        'ip_address',
        'user_agent',
        'violated_at',
    ];

    protected function casts(): array
    {
        return [
            'violated_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
