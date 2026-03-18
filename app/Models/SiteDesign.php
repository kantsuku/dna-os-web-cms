<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteDesign extends Model
{
    protected $fillable = [
        'site_id', 'name', 'tokens', 'component_styles',
        'layout_config', 'custom_css', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'component_styles' => 'array',
            'layout_config' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
