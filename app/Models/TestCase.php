<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestCase extends Model
{
    /** @use HasFactory<\Database\Factories\TestCaseFactory> */
    use HasFactory;
    use HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'activity_id',
        'title',
        'input',
        'output',
        'score',
        'order',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'order' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
