<?php

namespace App\Services\Web;

use Illuminate\Support\Str;

class SectionParseService
{
    /**
     * HTMLをcom-section単位にセクション分割する
     *
     * @return array セクション配列 [{section_id, heading, content_html, lock_status, last_modified_by, last_modified_at, order}]
     */
    public function parse(string $html): array
    {
        $html = trim($html);
        if (empty($html)) {
            return [];
        }

        $fragments = $this->splitBySectionTag($html);

        if (empty($fragments)) {
            // パース失敗: HTML全体を1セクションとして扱う
            return [$this->buildSection('sec_01', $html, 1)];
        }

        $sections = [];
        foreach ($fragments as $i => $fragment) {
            $sectionId = sprintf('sec_%02d', $i + 1);
            $order = $i + 1;
            $sections[] = $this->buildSection($sectionId, $fragment, $order);
        }

        return $sections;
    }

    /**
     * セクション配列から final_html を組み立てる
     */
    public function buildFinalHtml(array $sections): string
    {
        $parts = [];
        foreach ($sections as $section) {
            $parts[] = $section['content_html'] ?? '';
        }
        return implode("\n\n", $parts);
    }

    /**
     * 既存セクション配列と新しいセクション配列をマージする
     * ロックされたセクションは旧世代の内容を維持する
     *
     * @param array $oldSections 旧世代のセクション
     * @param array $newSections 新世代のセクション
     * @return array マージ済みセクション + スキップ情報
     */
    public function mergeWithLocks(array $oldSections, array $newSections): array
    {
        $oldMap = collect($oldSections)->keyBy('section_id');
        $merged = [];
        $skipped = [];

        foreach ($newSections as $newSec) {
            $id = $newSec['section_id'];
            $oldSec = $oldMap->get($id);

            if ($oldSec && in_array($oldSec['lock_status'] ?? 'unlocked', ['human_locked', 'system_locked'])) {
                // ロックされたセクションは旧世代を引き継ぎ
                $merged[] = $oldSec;
                $skipped[] = [
                    'section_id' => $id,
                    'heading' => $oldSec['heading'] ?? '',
                    'lock_status' => $oldSec['lock_status'],
                    'reason' => 'セクションがロックされているため再生成をスキップしました',
                ];
            } else {
                $merged[] = $newSec;
            }
        }

        return [
            'sections' => $merged,
            'skipped' => $skipped,
        ];
    }

    /**
     * <section class="com-section ..."> タグを境界としてHTMLを分割する
     */
    private function splitBySectionTag(string $html): array
    {
        // <section で始まり com-section を含む開始タグを検出
        $pattern = '/<section\b[^>]*\bcom-section\b[^>]*>/i';

        // 開始タグの位置を全て見つける
        preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return [];
        }

        $positions = array_map(fn($m) => $m[1], $matches[0]);
        $fragments = [];

        for ($i = 0; $i < count($positions); $i++) {
            $start = $positions[$i];
            $end = isset($positions[$i + 1]) ? $positions[$i + 1] : strlen($html);
            $fragment = trim(substr($html, $start, $end - $start));

            if (!empty($fragment)) {
                $fragments[] = $fragment;
            }
        }

        // 最初のセクションタグより前にコンテンツがある場合（ヒーロー等）
        $beforeFirst = trim(substr($html, 0, $positions[0]));
        if (!empty($beforeFirst)) {
            array_unshift($fragments, $beforeFirst);
        }

        return $fragments;
    }

    /**
     * セクションデータ構造を組み立てる
     */
    private function buildSection(string $sectionId, string $contentHtml, int $order): array
    {
        return [
            'section_id' => $sectionId,
            'heading' => $this->extractHeading($contentHtml),
            'content_html' => $contentHtml,
            'lock_status' => 'unlocked',
            'last_modified_by' => 'ai',
            'last_modified_at' => now()->toIso8601String(),
            'order' => $order,
        ];
    }

    /**
     * HTML内の最初の見出しテキストを抽出する
     */
    private function extractHeading(string $html): string
    {
        // h2, h3, h4 の順で探す
        if (preg_match('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/is', $html, $m)) {
            $text = strip_tags($m[1]);
            return Str::limit(trim($text), 80);
        }
        return '';
    }
}
