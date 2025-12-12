<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'access_code',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSelectedAttributes($query)
    {
        return $query->select('id', 'user_id', 'name', 'access_code', 'is_active', 'created_at');
    }
}
