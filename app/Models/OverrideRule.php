<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OverrideRule extends Model
{
    protected $fillable = [
        'section_id',
        'policy',
        'reason',
        'set_by',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function setter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
