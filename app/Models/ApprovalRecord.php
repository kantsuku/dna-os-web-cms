<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRecord extends Model
{
    protected $fillable = [
        'variant_id',
        'action',
        'reviewer_id',
        'notes',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ContentVariant::class, 'variant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
