<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'site_id', 'parent_id', 'slug', 'title', 'page_type',
        'content_classification', 'template_key', 'meta', 'dna_source_key',
        'treatment_key', 'sort_order', 'current_generation_id',
        'is_published', 'status',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
        ];
    }

    // ── リレーション ──

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
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

    public function channelTasks(): HasMany
    {
        return $this->hasMany(ChannelTask::class, 'target_page_id');
    }

    // ── コンテンツ分類の自動判定 ──

    public static function classifyByPageType(string $pageType): string
    {
        return match ($pageType) {
            'blog', 'news' => 'assisted',
            'case', 'exception' => 'exception',
            default => 'standard',
        };
    }

    // ── セクション操作のヘルパー ──

    public function hasSlot(string $slotName): bool
    {
        $gen = $this->currentGeneration;
        if (!$gen || !$gen->sections) {
            return false;
        }
        foreach ($gen->sections as $section) {
            if (($section['section_id'] ?? '') === $slotName) {
                return true;
            }
        }
        return false;
    }

    public function getSlotHtml(string $slotName): string
    {
        $gen = $this->currentGeneration;
        if (!$gen || !$gen->sections) {
            return '';
        }
        foreach ($gen->sections as $section) {
            if (($section['section_id'] ?? '') === $slotName) {
                return $section['content_html'] ?? '';
            }
        }
        return '';
    }
}
