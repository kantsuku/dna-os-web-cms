<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageGeneration extends Model
{
    protected $fillable = [
        'page_id', 'generation', 'source', 'source_url',
        'content_html', 'content_text', 'meta_json',
        'human_patch', 'patch_reason', 'patched_by', 'patched_at',
        'final_html', 'status',
    ];

    protected function casts(): array
    {
        return [
            'meta_json' => 'array',
            'human_patch' => 'array',
            'patched_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function patcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patched_by');
    }

    public function hasHumanPatch(): bool
    {
        return !empty($this->human_patch);
    }
}
