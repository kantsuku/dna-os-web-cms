<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrchestrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'clinic_id', 'event_type',
        'source_type', 'source_id', 'detail',
    ];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ── ファクトリメソッド ──

    public static function log(
        string $clinicId,
        string $eventType,
        ?string $sourceType = null,
        ?string $sourceId = null,
        ?array $detail = null,
    ): static {
        return static::create([
            'clinic_id' => $clinicId,
            'event_type' => $eventType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'detail' => $detail,
        ]);
    }

    // ── スコープ ──

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
