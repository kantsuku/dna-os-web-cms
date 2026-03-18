<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishRecord extends Model
{
    protected $fillable = [
        'site_id',
        'pages_json',
        'snapshot_path',
        'deploy_status',
        'deployed_by',
        'deployed_at',
        'rollback_of',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'pages_json' => 'array',
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
        return $this->belongsTo(PublishRecord::class, 'rollback_of');
    }
}
