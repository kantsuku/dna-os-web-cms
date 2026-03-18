<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'approvable_type', 'approvable_id',
        'approval_type', 'approval_level',
        'approved_by', 'comment', 'diff_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'diff_snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── ファクトリメソッド ──

    public static function recordApproval(
        string $type,
        string $id,
        int $userId,
        string $level = 'standard',
        ?string $comment = null,
        ?array $diffSnapshot = null,
    ): static {
        return static::create([
            'approvable_type' => $type,
            'approvable_id' => $id,
            'approval_type' => 'approve',
            'approval_level' => $level,
            'approved_by' => $userId,
            'comment' => $comment,
            'diff_snapshot' => $diffSnapshot,
        ]);
    }

    public static function recordRejection(
        string $type,
        string $id,
        int $userId,
        string $comment,
        string $level = 'standard',
    ): static {
        return static::create([
            'approvable_type' => $type,
            'approvable_id' => $id,
            'approval_type' => 'reject',
            'approval_level' => $level,
            'approved_by' => $userId,
            'comment' => $comment,
        ]);
    }
}
