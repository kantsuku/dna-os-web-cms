<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedContentSource extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'clinic_id', 'page_id', 'source_type',
        'source_url', 'source_meta', 'fetched_html',
        'fetched_at', 'page_generation_id',
    ];

    protected function casts(): array
    {
        return [
            'source_meta' => 'array',
            'fetched_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function pageGeneration(): BelongsTo
    {
        return $this->belongsTo(PageGeneration::class);
    }
}
