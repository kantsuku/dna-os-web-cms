<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeployRecord extends Model
{
    protected $fillable = [
        'site_id', 'generation_snapshot', 'build_path',
        'deploy_status', 'deployed_by', 'deployed_at',
        'rollback_of', 'error_log',
    ];

    protected function casts(): array
    {
        return [
            'generation_snapshot' => 'array',
            'deployed_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function deployer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }

    public function rollbackSource(): BelongsTo
    {
        return $this->belongsTo(DeployRecord::class, 'rollback_of');
    }
}
