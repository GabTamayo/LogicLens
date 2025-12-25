<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'access_code',
        'cover_photo',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLinks(): HasMany
    {
        return $this->hasMany(ActivityLink::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'name', 'access_code', 'cover_photo', 'created_at');
    }

    public static function getAvailableCoverPhotos(): array
    {
        $coverPhotosPath = public_path('images/cover-photos');

        if (! is_dir($coverPhotosPath)) {
            return [];
        }

        $files = array_diff(scandir($coverPhotosPath), ['.', '..']);

        return collect($files)
            ->filter(fn ($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp']))
            ->map(fn ($file) => [
                'name' => $file,
                'path' => '/images/cover-photos/'.$file,
            ])
            ->values()
            ->toArray();
    }
}
