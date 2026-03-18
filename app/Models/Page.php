<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'site_id',
        'slug',
        'title',
        'page_type',
        'template_name',
        'meta_description',
        'og_image_path',
        'status',
        'publish_version',
        'sort_order',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
