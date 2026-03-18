<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicDesign extends Model
{
    protected $fillable = [
        'clinic_id', 'name', 'tokens',
        'tone_and_manner', 'prohibited_terms', 'recommended_terms',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'tone_and_manner' => 'array',
            'prohibited_terms' => 'array',
            'recommended_terms' => 'array',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * 医院トークンとサイトトークンをマージして最終トークンを返す
     */
    public function mergeWithSiteTokens(?array $siteTokens): array
    {
        $clinicTokens = $this->tokens ?? [];
        return array_merge($clinicTokens, $siteTokens ?? []);
    }
}
