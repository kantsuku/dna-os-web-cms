<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelTask extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'strategic_task_id', 'channel', 'task_type',
        'title', 'instruction',
        'target_site_id', 'target_page_id', 'target_sections',
        'input_data', 'status', 'execution_log', 'result', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'target_sections' => 'array',
            'input_data' => 'array',
            'execution_log' => 'array',
            'result' => 'array',
        ];
    }

    // ── リレーション ──

    public function strategicTask(): BelongsTo
    {
        return $this->belongsTo(StrategicTask::class);
    }

    public function targetSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'target_site_id');
    }

    public function targetPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'target_page_id');
    }

    public function approvalRecords(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class, 'approvable_id')
            ->where('approvable_type', 'channel_task');
    }

    // ── ID生成 ──

    public static function generateId(string $channel = 'web'): string
    {
        $prefix = 'CT-' . strtoupper($channel);
        $date = now()->format('Ymd');
        $latest = static::where('id', 'like', "{$prefix}-{$date}-%")->orderByDesc('id')->first();
        $seq = $latest ? (int) substr($latest->id, -3) + 1 : 1;
        return sprintf('%s-%s-%03d', $prefix, $date, $seq);
    }

    // ── ログ追記 ──

    public function appendLog(string $action, string $detail = ''): void
    {
        $log = $this->execution_log ?? [];
        $log[] = [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'detail' => $detail,
        ];
        $this->update(['execution_log' => $log]);
    }

    // ── スコープ ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('target_site_id', $siteId);
    }

    public function scopeReviewReady($query)
    {
        return $query->where('status', 'review_ready');
    }
}
