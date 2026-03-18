<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageGeneration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentImportService
{
    /**
     * Google Docs URL からコンテンツを取得
     */
    public function fetchFromUrl(string $url): string
    {
        // Google Docs の場合は export URL に変換
        $fetchUrl = $this->resolveGoogleUrl($url);

        $response = Http::timeout(30)->get($fetchUrl);

        if ($response->failed()) {
            throw new \RuntimeException('コンテンツ取得失敗: HTTP ' . $response->status());
        }

        return $response->body();
    }

    /**
     * ページに新世代として取り込み
     */
    public function importToPage(Page $page, string $html, string $sourceUrl, ?int $userId = null): PageGeneration
    {
        $nextGen = ($page->generations()->max('generation') ?? 0) + 1;

        // 既存の received/ready 世代を superseded に
        $page->generations()
            ->whereIn('status', ['received', 'ready'])
            ->update(['status' => 'superseded']);

        $generation = PageGeneration::create([
            'page_id' => $page->id,
            'generation' => $nextGen,
            'source' => 'ai_generated',
            'source_url' => $sourceUrl,
            'content_html' => $html,
            'content_text' => strip_tags($html),
            'meta_json' => [
                'imported_at' => now()->toIso8601String(),
                'imported_by' => $userId,
                'source_url' => $sourceUrl,
            ],
            'final_html' => $html,
            'status' => 'received',
        ]);

        return $generation;
    }

    /**
     * 微細編集の差分を記録
     */
    public function applyPatch(PageGeneration $generation, string $editedHtml, string $reason, int $userId): void
    {
        $original = $generation->content_html;
        $patch = $this->computeDiff($original, $editedHtml);

        $generation->update([
            'human_patch' => $patch,
            'patch_reason' => $reason,
            'patched_by' => $userId,
            'patched_at' => now(),
            'final_html' => $editedHtml,
        ]);
    }

    /**
     * Google Docs/Drive URL を取得可能なURLに変換
     */
    private function resolveGoogleUrl(string $url): string
    {
        // Google Docs: /d/DOC_ID/edit → export as HTML
        if (preg_match('#docs\.google\.com/document/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return "https://docs.google.com/document/d/{$m[1]}/export?format=txt";
        }

        // Google Drive direct link
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        // GAS WebApp（記事生成の出力API）
        if (str_contains($url, 'script.google.com')) {
            return $url;
        }

        // その他: そのまま
        return $url;
    }

    /**
     * 簡易差分計算（行ベース）
     */
    private function computeDiff(string $original, string $edited): array
    {
        $origLines = explode("\n", $original);
        $editLines = explode("\n", $edited);
        $changes = [];

        $maxLines = max(count($origLines), count($editLines));
        for ($i = 0; $i < $maxLines; $i++) {
            $origLine = $origLines[$i] ?? null;
            $editLine = $editLines[$i] ?? null;

            if ($origLine !== $editLine) {
                $changes[] = [
                    'line' => $i + 1,
                    'original' => $origLine,
                    'edited' => $editLine,
                ];
            }
        }

        return $changes;
    }
}
