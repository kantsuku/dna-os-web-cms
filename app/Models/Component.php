<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'key', 'name', 'category', 'html_template',
        'default_styles', 'preview_html', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_styles' => 'array',
        ];
    }
}
