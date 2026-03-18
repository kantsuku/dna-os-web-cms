<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategicTask extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'clinic_id', 'trigger_type', 'trigger_source_id',
        'title', 'description', 'intent',
        'priority', 'risk_level', 'target_channels',
        'status', 'created_by', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'target_channels' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    // ── リレーション ──

    public function channelTasks(): HasMany
    {
        return $this->hasMany(ChannelTask::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalRecords(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class, 'approvable_id')
            ->where('approvable_type', 'strategic_task');
    }

    // ── ID生成 ──

    public static function generateId(): string
    {
        $date = now()->format('Ymd');
        $latest = static::where('id', 'like', "ST-{$date}-%")->orderByDesc('id')->first();
        $seq = $latest ? (int) substr($latest->id, -3) + 1 : 1;
        return sprintf('ST-%s-%03d', $date, $seq);
    }

    // ── ステータス操作 ──

    public function canBeApproved(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
        $this->channelTasks()->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'cancelled']);
    }

    // ── スコープ ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'in_progress']);
    }
}
