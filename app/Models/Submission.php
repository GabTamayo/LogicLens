<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'language',
    ];

    public function activityLink(): BelongsTo
    {
        return $this->belongsTo(ActivityLink::class);
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'student_name', 'student_email', 'student_no', 'file_path', 'language', 'created_at');
    }

    public function scopeFilterByStudent($query, ?string $name = null, ?string $number = null)
    {
        return $query
            ->when($name, fn($q, $search) => $q->where('student_name', 'like', '%' . $search . '%'))
            ->when($number, fn($q, $search) => $q->where('student_no', 'like', '%' . $search . '%'));
    }

    public function attachFileContent()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            $this->file_content = Storage::disk('public')->get($this->file_path);
            $this->file_extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        }
        return $this;
    }

    public function activityLanguage(): ?string
    {
        return $this->activityLink?->activity?->language;
    }
}
