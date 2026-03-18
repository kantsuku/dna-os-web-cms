<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Site extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'domain',
        'xserver_host',
        'xserver_ftp_user',
        'xserver_ftp_pass',
        'xserver_deploy_path',
        'template_set',
        'status',
        'wp_site_url',
    ];

    protected $hidden = [
        'xserver_ftp_pass',
    ];

    protected function casts(): array
    {
        return [
            'xserver_ftp_pass' => 'encrypted',
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('sort_order');
    }

    public function publishRecords(): HasMany
    {
        return $this->hasMany(PublishRecord::class)->orderByDesc('created_at');
    }

    public function exceptionContents(): HasMany
    {
        return $this->hasMany(ExceptionContent::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class)->orderByDesc('started_at');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
