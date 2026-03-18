<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImprovementReport extends Model
{
    protected $fillable = [
        'clinic_id', 'site_id', 'report_type',
        'title', 'summary', 'findings',
        'generated_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'findings' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
