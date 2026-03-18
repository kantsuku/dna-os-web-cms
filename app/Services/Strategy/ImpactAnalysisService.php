<?php

namespace App\Services\Strategy;

use App\Models\Page;
use App\Models\Site;

class ImpactAnalysisService
{
    /**
     * DNA-OSシート名 → 影響するページの対応テーブル
     */
    private const SHEET_PAGE_MAP = [
        '04_Treatment_Policy' => ['page_type' => 'lower', 'match_field' => 'dna_source_key'],
        '03_DNA_Master'       => ['page_type' => null, 'slugs' => ['/about'], 'check_all_tone' => true],
        '10_Staff_Master'     => ['page_type' => null, 'slugs' => ['/staff', '/about']],
        '11_Recruitment_Policy' => ['page_type' => null, 'slugs' => ['/recruit', '/recruitment']],
        '16_Credo_Master'     => ['page_type' => null, 'slugs' => ['/about']],
        '31_Tone_And_Manner'  => ['page_type' => null, 'check_all_tone' => true],
        '00_Clinic'           => ['page_type' => null, 'affects_common' => true],
        '07_Referral_Network' => ['page_type' => null, 'slugs' => ['/about']],
        '60_Strategy_State'   => ['page_type' => null, 'no_web_impact' => true],
        '61_Strategy_Metrics' => ['page_type' => null, 'no_web_impact' => true],
    ];

    /**
     * DNA-OS変更からWebチャネルへの影響ページを特定する
     *
     * @param array $change {destination_sheet, destination_field, destination_record_id, proposed_value}
     * @return array [{page, impact_level, reason}]
     */
    public function analyzeImpact(Site $site, array $change): array
    {
        $sheet = $change['destination_sheet'] ?? '';
        $field = $change['destination_field'] ?? '';
        $recordId = $change['destination_record_id'] ?? '';

        $mapping = self::SHEET_PAGE_MAP[$sheet] ?? null;
        if (!$mapping) {
            return [];
        }

        // 戦略系シートはWeb影響なし
        if (!empty($mapping['no_web_impact'])) {
            return [];
        }

        $impacts = [];

        // 診療方針変更 → 該当診療ページ
        if (!empty($mapping['match_field'])) {
            $pages = $site->pages()
                ->where($mapping['match_field'], $recordId)
                ->orWhere('treatment_key', $recordId)
                ->get();

            foreach ($pages as $page) {
                $impacts[] = [
                    'page' => $page,
                    'impact_level' => 'high',
                    'reason' => "{$sheet}の{$field}が変更されたため、{$page->title}を更新する必要があります",
                    'task_type' => 'update_content',
                ];
            }
        }

        // 特定slugへの影響
        if (!empty($mapping['slugs'])) {
            $pages = $site->pages()->whereIn('slug', $mapping['slugs'])->get();
            foreach ($pages as $page) {
                $impacts[] = [
                    'page' => $page,
                    'impact_level' => 'medium',
                    'reason' => "{$sheet}の変更が{$page->title}に影響する可能性があります",
                    'task_type' => 'update_content',
                ];
            }
        }

        // トーン変更 → 全ページチェック
        if (!empty($mapping['check_all_tone'])) {
            $impacts[] = [
                'page' => null,
                'impact_level' => 'low',
                'reason' => "{$sheet}の変更により全ページのトーン整合性チェックが必要です",
                'task_type' => 'check_quality',
            ];
        }

        // 共通情報（フッター等）
        if (!empty($mapping['affects_common'])) {
            $impacts[] = [
                'page' => null,
                'impact_level' => 'medium',
                'reason' => "医院基本情報が変更されました。共通テンプレート（ヘッダー/フッター）の更新が必要な可能性があります",
                'task_type' => 'update_content',
            ];
        }

        return $impacts;
    }

    /**
     * 複数の変更を一括分析して影響マップを生成する
     *
     * @return array ['high' => [...], 'medium' => [...], 'low' => [...]]
     */
    public function analyzeMultipleChanges(Site $site, array $changes): array
    {
        $allImpacts = [];
        $seenPages = [];

        foreach ($changes as $change) {
            $impacts = $this->analyzeImpact($site, $change);
            foreach ($impacts as $impact) {
                $pageId = $impact['page']?->id ?? 'site_wide';
                // 同一ページへの重複排除（最も高い影響度を採用）
                if (!isset($seenPages[$pageId]) || $this->levelRank($impact['impact_level']) > $this->levelRank($seenPages[$pageId])) {
                    $seenPages[$pageId] = $impact['impact_level'];
                    $allImpacts[$pageId] = $impact;
                }
            }
        }

        // 影響度別にグループ化
        $grouped = ['high' => [], 'medium' => [], 'low' => []];
        foreach ($allImpacts as $impact) {
            $grouped[$impact['impact_level']][] = $impact;
        }

        return $grouped;
    }

    private function levelRank(string $level): int
    {
        return match ($level) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 0,
        };
    }
}
