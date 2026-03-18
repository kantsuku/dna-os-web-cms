<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Section extends Model
{
    protected $fillable = [
        'page_id',
        'section_key',
        'sort_order',
        'content_source_type',
        'content_source_ref',
        'is_human_edited',
    ];

    protected function casts(): array
    {
        return [
            'content_source_ref' => 'array',
            'is_human_edited' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ContentVariant::class)->orderByDesc('version');
    }

    public function overrideRule(): HasOne
    {
        return $this->hasOne(OverrideRule::class);
    }

    /**
     * 現在有効なバリアントを取得（published > approved > draft の優先順）
     */
    public function activeVariant(): HasOne
    {
        return $this->hasOne(ContentVariant::class)
            ->whereIn('status', ['published', 'approved', 'draft'])
            ->orderByRaw("FIELD(status, 'published', 'approved', 'draft')")
            ->orderByDesc('version');
    }

    /**
     * 公開中のバリアントを取得
     */
    public function publishedVariant(): HasOne
    {
        return $this->hasOne(ContentVariant::class)
            ->where('status', 'published')
            ->orderByDesc('version');
    }

    /**
     * 上書き制御ポリシーを取得
     */
    public function getOverridePolicyAttribute(): string
    {
        return $this->overrideRule?->policy ?? $this->getDefaultPolicy();
    }

    private function getDefaultPolicy(): string
    {
        return match ($this->content_source_type) {
            'exception', 'client_post' => 'locked',
            'manual' => 'manual_only',
            default => 'auto_sync',
        };
    }
}
