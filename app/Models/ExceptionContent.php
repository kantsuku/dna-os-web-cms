<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExceptionContent extends Model
{
    protected $fillable = [
        'page_id', 'content_type', 'title', 'content_html',
        'structured_data',
        'ai_enhanced_html', 'use_ai_version',
        'compliance_notes', 'compliance_check',
        'status', 'visibility', 'publish_expires_at',
        'first_approved_by', 'first_approved_at',
        'final_approved_by', 'final_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'structured_data' => 'array',
            'compliance_check' => 'array',
            'use_ai_version' => 'boolean',
            'publish_expires_at' => 'datetime',
            'first_approved_at' => 'datetime',
            'final_approved_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function firstApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_approved_by');
    }

    public function finalApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approved_by');
    }

    public function approvalRecords(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class, 'approvable_id')
            ->where('approvable_type', 'exception_content');
    }

    public function getActiveHtmlAttribute(): string
    {
        return $this->use_ai_version && $this->ai_enhanced_html
            ? $this->ai_enhanced_html
            : $this->content_html;
    }

    public function needsFirstReview(): bool
    {
        return $this->status === 'first_review';
    }

    public function needsFinalReview(): bool
    {
        return $this->status === 'final_review';
    }

    public function isExpired(): bool
    {
        return $this->publish_expires_at && $this->publish_expires_at->isPast();
    }
}
