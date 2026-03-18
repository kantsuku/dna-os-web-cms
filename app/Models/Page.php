<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'site_id', 'slug', 'title', 'page_type',
        'treatment_key', 'sort_order', 'current_generation_id', 'status',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function generations(): HasMany
    {
        return $this->hasMany(PageGeneration::class)->orderByDesc('generation');
    }

    public function currentGeneration(): BelongsTo
    {
        return $this->belongsTo(PageGeneration::class, 'current_generation_id');
    }

    public function latestGeneration(): HasMany
    {
        return $this->hasMany(PageGeneration::class)->orderByDesc('generation')->limit(1);
    }

    public function exceptionContents(): HasMany
    {
        return $this->hasMany(ExceptionContent::class);
    }
}
