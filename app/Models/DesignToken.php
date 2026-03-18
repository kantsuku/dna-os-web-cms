<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignToken extends Model
{
    protected $fillable = [
        'category', 'key', 'value', 'label', 'sort_order',
    ];

    /**
     * 全トークンをCSS Custom Properties形式で返す
     */
    public static function toCssVariables(): string
    {
        $tokens = static::orderBy('sort_order')->get();
        $lines = [];
        foreach ($tokens as $token) {
            $lines[] = "  --acms-{$token->key}: {$token->value};";
        }
        return ":root {\n" . implode("\n", $lines) . "\n}";
    }
}
