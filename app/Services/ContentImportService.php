<?php

namespace App\Services;

use App\Models\GeneratedContentSource;
use App\Models\Page;
use App\Models\PageGeneration;
use App\Services\Web\SectionParseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentImportService
{
    public function __construct(
        private SectionParseService $sectionParser,
    ) {}

    /**
     * Google Docs URL からコンテンツを取得
     */
    public function fetchFromUrl(string $url): string
    {
        $fetchUrl = $this->resolveGoogleUrl($url);

        $response = Http::timeout(30)->get($fetchUrl);

        if ($response->failed()) {
            throw new \RuntimeException('コンテンツ取得失敗: HTTP ' . $response->status());
        }

        return $response->body();
    }

    /**
     * ページに新世代として取り込み（v3: セクション分割対応）
     */
    public function importToPage(
        Page $page,
        string $html,
        string $sourceUrl,
        ?int $userId = null,
        string $sourceType = 'google_docs',
    ): PageGeneration {
        $nextGen = ($page->generations()->max('generation') ?? 0) + 1;

        // セクション分割
        $newSections = $this->sectionParser->parse($html);

        // ロック済みセクションとのマージ
        $currentGen = $page->currentGeneration;
        $skipped = [];
        if ($currentGen && !empty($currentGen->sections)) {
            $mergeResult = $this->sectionParser->mergeWithLocks($currentGen->sections, $newSections);
            $newSections = $mergeResult['sections'];
            $skipped = $mergeResult['skipped'];
        }

        $finalHtml = $this->sectionParser->buildFinalHtml($newSections);

        // 既存の draft/received/ready 世代を superseded に
        $page->generations()
            ->whereIn('status', ['draft', 'received', 'ready'])
            ->update(['status' => 'superseded']);

        $generation = PageGeneration::create([
            'page_id' => $page->id,
            'generation' => $nextGen,
            'source' => 'ai_generated',
            'source_url' => $sourceUrl,
            'sections' => $newSections,
            'content_html' => $html,
            'content_text' => strip_tags($html),
            'meta_json' => [
                'imported_at' => now()->toIso8601String(),
                'imported_by' => $userId,
                'source_url' => $sourceUrl,
                'sections_count' => count($newSections),
                'skipped_sections' => $skipped,
            ],
            'final_html' => $finalHtml,
            'status' => 'draft',
        ]);

        // 生成元記録
        GeneratedContentSource::create([
            'clinic_id' => $page->site->clinic_id,
            'page_id' => $page->id,
            'source_type' => $sourceType,
            'source_url' => $sourceUrl,
            'source_meta' => [
                'treatment_key' => $page->treatment_key ?? $page->dna_source_key,
                'fetched_by' => $userId,
            ],
            'fetched_html' => $html,
            'fetched_at' => now(),
            'page_generation_id' => $generation->id,
        ]);

        if (!empty($skipped)) {
            Log::info('セクションスキップ発生', [
                'page_id' => $page->id,
                'generation' => $nextGen,
                'skipped' => $skipped,
            ]);
        }

        return $generation;
    }

    /**
     * マークアップTXTからの直接取り込み
     */
    public function importFromMarkupText(
        Page $page,
        string $markupHtml,
        ?int $userId = null,
    ): PageGeneration {
        return $this->importToPage($page, $markupHtml, '', $userId, 'markup_txt');
    }

    /**
     * セクション単位の微細編集を記録（v3）
     */
    public function applySectionPatch(
        PageGeneration $generation,
        string $sectionId,
        string $editedHtml,
        string $reason,
        int $userId,
        bool $lockAfterEdit = false,
    ): void {
        $sections = $generation->sections ?? [];
        $patches = $generation->human_patch ?? [];

        foreach ($sections as $i => $section) {
            if (($section['section_id'] ?? '') === $sectionId) {
                $oldHtml = $section['content_html'] ?? '';

                // パッチ記録
                $patches[] = [
                    'section_id' => $sectionId,
                    'patch' => $this->computeDiff($oldHtml, $editedHtml),
                    'reason' => $reason,
                    'user_id' => $userId,
                    'at' => now()->toIso8601String(),
                ];

                // セクション更新
                $sections[$i]['content_html'] = $editedHtml;
                $sections[$i]['last_modified_by'] = 'human:' . $userId;
                $sections[$i]['last_modified_at'] = now()->toIso8601String();

                if ($lockAfterEdit) {
                    $sections[$i]['lock_status'] = 'human_locked';
                }

                break;
            }
        }

        $finalHtml = $this->sectionParser->buildFinalHtml($sections);

        $generation->update([
            'sections' => $sections,
            'human_patch' => $patches,
            'patch_reason' => $reason,
            'patched_by' => $userId,
            'patched_at' => now(),
            'final_html' => $finalHtml,
        ]);
    }

    /**
     * セクションのロック状態を変更する
     */
    public function toggleSectionLock(
        PageGeneration $generation,
        string $sectionId,
        string $lockStatus,
    ): void {
        $sections = $generation->sections ?? [];

        foreach ($sections as $i => $section) {
            if (($section['section_id'] ?? '') === $sectionId) {
                $sections[$i]['lock_status'] = $lockStatus;
                break;
            }
        }

        $generation->update(['sections' => $sections]);
    }

    /**
     * v2互換: ページ全体の微細編集（セクションなしの場合）
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
        if (preg_match('#docs\.google\.com/document/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return "https://docs.google.com/document/d/{$m[1]}/export?format=txt";
        }

        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        if (str_contains($url, 'script.google.com')) {
            return $url;
        }

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
