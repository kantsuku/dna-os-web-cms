<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Clinic extends Model
{
    protected $fillable = [
        'clinic_id', 'name', 'gas_webapp_url', 'settings', 'status',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    // ── リレーション ──

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'clinic_ref_id');
    }

    public function design(): HasOne
    {
        return $this->hasOne(ClinicDesign::class);
    }

    public function clinicComponents(): HasMany
    {
        return $this->hasMany(ClinicComponent::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function strategicTasks(): HasMany
    {
        return $this->hasMany(StrategicTask::class, 'clinic_ref_id');
    }

    public function improvementReports(): HasMany
    {
        return $this->hasMany(ImprovementReport::class, 'clinic_ref_id');
    }

    public function freeInputRequests(): HasMany
    {
        return $this->hasMany(FreeInputRequest::class, 'clinic_ref_id');
    }

    // ── ヘルパー ──

    public function hpSite(): ?Site
    {
        return $this->sites()->where('site_type', 'hp')->first();
    }

    public function activeSites()
    {
        return $this->sites()->where('status', 'active');
    }

    public function pendingTaskCount(): int
    {
        return $this->strategicTasks()->where('status', 'pending_approval')->count();
    }
}
