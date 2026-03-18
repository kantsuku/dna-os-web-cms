<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicComponent extends Model
{
    protected $fillable = [
        'clinic_id', 'key', 'name', 'category',
        'html_template', 'default_styles', 'preview_html',
        'description', 'variants', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_styles' => 'array',
            'variants' => 'array',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
