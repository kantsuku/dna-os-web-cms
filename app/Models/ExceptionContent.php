<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExceptionContent extends Model
{
    protected $fillable = [
        'site_id',
        'content_type',
        'title',
        'content_html',
        'risk_level',
        'compliance_notes',
        'requires_specialist_review',
        'status',
        'linked_section_id',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_specialist_review' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function linkedSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'linked_section_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
