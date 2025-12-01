<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        if ($this->file_path && Storage::disk(env('FILESYSTEM_DISK'))->exists($this->file_path)) {
            $this->file_content = Storage::disk(env('FILESYSTEM_DISK'))->get($this->file_path);
        }
        return $this;
    }

    public function activityLanguage(): ?string
    {
        return $this->activityLink?->activity?->language;
    }
}
