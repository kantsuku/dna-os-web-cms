<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'site_id',
        'sync_type',
        'sections_updated',
        'sections_skipped',
        'sections_conflicted',
        'details',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
