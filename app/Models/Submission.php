<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'activity_link_id',
        'student_name',
        'student_email',
        'student_no',
        'file_path',
    ];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'activity_link_id', 'student_name', 'student_email', 'student_no', 'file_path', 'created_at');
    }
}
