<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'key', 'migration_key', 'name', 'category', 'html_template',
        'default_styles', 'preview_html', 'description', 'variants', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_styles' => 'array',
            'variants' => 'array',
        ];
    }

    /**
     * 将来のコンポーネントキー移行用
     * migration_keyが設定されていればそれを、なければ現行keyを返す
     */
    public function getActiveKeyAttribute(): string
    {
        if (config('acms.use_migration_keys', false) && $this->migration_key) {
            return $this->migration_key;
        }
        return $this->key;
    }
}
