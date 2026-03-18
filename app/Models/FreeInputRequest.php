<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeInputRequest extends Model
{
    protected $fillable = [
        'clinic_id', 'site_id', 'raw_text',
        'ai_interpretation', 'interpretation_status',
        'strategic_task_id', 'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'ai_interpretation' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function strategicTask(): BelongsTo
    {
        return $this->belongsTo(StrategicTask::class);
    }

    public function isInterpreted(): bool
    {
        return $this->interpretation_status === 'interpreted';
    }
}
