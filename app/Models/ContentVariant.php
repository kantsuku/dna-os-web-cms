<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentVariant extends Model
{
    protected $fillable = [
        'section_id',
        'version',
        'source_type',
        'content_html',
        'content_raw',
        'original_content',
        'diff_from_original',
        'edited_by',
        'edit_reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'diff_from_original' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function approvalRecords(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class, 'variant_id');
    }
}
