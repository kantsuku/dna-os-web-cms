<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExceptionContent extends Model
{
    protected $fillable = [
        'page_id', 'content_type', 'title', 'content_html',
        'ai_enhanced_html', 'use_ai_version', 'compliance_notes', 'status',
    ];

    protected function casts(): array
    {
        return ['use_ai_version' => 'boolean'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getActiveHtmlAttribute(): string
    {
        return $this->use_ai_version && $this->ai_enhanced_html
            ? $this->ai_enhanced_html
            : $this->content_html;
    }
}
