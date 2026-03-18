<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    protected $fillable = [
        'clinic_id', 'clinic_ref_id', 'site_type', 'site_label',
        'name', 'domain',
        'xserver_host', 'xserver_ftp_user', 'xserver_ftp_pass', 'xserver_deploy_path',
        'gas_generator_url', 'design_id', 'status',
    ];

    protected $hidden = ['xserver_ftp_pass'];

    protected function casts(): array
    {
        return ['xserver_ftp_pass' => 'encrypted'];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_ref_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('sort_order');
    }

    public function getSiteTypeLabel(): string
    {
        return match ($this->site_type) {
            'hp' => 'HP（メインサイト）',
            'specialty' => '専門サイト',
            'recruitment' => '採用サイト',
            'lp' => 'LP',
            'gbp' => 'Googleビジネスプロフィール',
            'instagram' => 'Instagram',
            'blog_media' => 'ブログ / メディア',
            default => 'その他',
        };
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(SiteDesign::class, 'design_id');
    }

    public function designs(): HasMany
    {
        return $this->hasMany(SiteDesign::class);
    }

    public function deployRecords(): HasMany
    {
        return $this->hasMany(DeployRecord::class)->orderByDesc('created_at');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
