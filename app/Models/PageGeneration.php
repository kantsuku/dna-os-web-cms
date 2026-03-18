<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageGeneration extends Model
{
    protected $fillable = [
        'page_id', 'generation', 'source', 'source_url', 'source_task_id',
        'sections',
        'content_html', 'content_text', 'meta_json',
        'human_patch', 'patch_reason', 'patched_by', 'patched_at',
        'final_html', 'status',
        'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'meta_json' => 'array',
            'human_patch' => 'array',
            'patched_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function patcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patched_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hasHumanPatch(): bool
    {
        return !empty($this->human_patch);
    }

    public function hasSections(): bool
    {
        return !empty($this->sections);
    }

    // ── セクション操作 ──

    public function getSection(string $sectionId): ?array
    {
        foreach ($this->sections ?? [] as $section) {
            if (($section['section_id'] ?? '') === $sectionId) {
                return $section;
            }
        }
        return null;
    }

    public function updateSection(string $sectionId, array $data): void
    {
        $sections = $this->sections ?? [];
        foreach ($sections as $i => $section) {
            if (($section['section_id'] ?? '') === $sectionId) {
                $sections[$i] = array_merge($section, $data);
                break;
            }
        }
        $this->update(['sections' => $sections]);
    }

    public function getLockedSectionIds(): array
    {
        $locked = [];
        foreach ($this->sections ?? [] as $section) {
            if (in_array($section['lock_status'] ?? 'unlocked', ['human_locked', 'system_locked'])) {
                $locked[] = $section['section_id'];
            }
        }
        return $locked;
    }

    public function buildFinalHtml(): string
    {
        if (!$this->hasSections()) {
            return $this->content_html ?? '';
        }

        $parts = [];
        foreach ($this->sections as $section) {
            $parts[] = $section['content_html'] ?? '';
        }
        return implode("\n\n", $parts);
    }
}
