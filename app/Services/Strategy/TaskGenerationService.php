<?php

namespace App\Services\Strategy;

use App\Models\ChannelTask;
use App\Models\OrchestrationLog;
use App\Models\Site;
use App\Models\StrategicTask;

class TaskGenerationService
{
    public function __construct(
        private ImpactAnalysisService $impactAnalysis,
    ) {}

    /**
     * DNA-OS変更から戦略タスク + チャネルタスクを生成する
     */
    public function generateFromDnaChanges(Site $site, array $changes): ?StrategicTask
    {
        if (empty($changes)) {
            return null;
        }

        $impactMap = $this->impactAnalysis->analyzeMultipleChanges($site, $changes);
        $totalImpacts = count($impactMap['high']) + count($impactMap['medium']) + count($impactMap['low']);

        if ($totalImpacts === 0) {
            return null;
        }

        // リスクレベル判定
        $riskLevel = !empty($impactMap['high']) ? 'high' : (!empty($impactMap['medium']) ? 'medium' : 'low');
        $priority = !empty($impactMap['high']) ? 'high' : 'medium';

        // 戦略タスク生成
        $proposalIds = array_filter(array_column($changes, 'proposal_id'));
        $sheetNames = array_unique(array_column($changes, 'destination_sheet'));

        $st = StrategicTask::create([
            'id' => StrategicTask::generateId(),
            'clinic_id' => $site->clinic_id,
            'trigger_type' => 'dna_update',
            'trigger_source_id' => implode(',', $proposalIds),
            'title' => $this->buildTitle($sheetNames, $totalImpacts),
            'description' => $this->buildDescription($changes, $impactMap),
            'intent' => "DNA-OSの更新内容をWebサイトに反映する（影響ページ: {$totalImpacts}件）",
            'priority' => $priority,
            'risk_level' => $riskLevel,
            'target_channels' => ['web'],
            'status' => 'pending_approval',
            'created_by' => 'ai_chief',
        ]);

        // チャネルタスク分解
        $this->generateChannelTasks($st, $site, $impactMap, $changes);

        OrchestrationLog::log(
            $site->clinic_id,
            'task_generated',
            'strategic_task',
            $st->id,
            ['impacts' => $totalImpacts, 'risk_level' => $riskLevel],
        );

        return $st;
    }

    /**
     * フリー入力から戦略タスク + チャネルタスクを生成する
     */
    public function generateFromFreeInput(
        Site $site,
        string $title,
        string $description,
        ?int $targetPageId = null,
        ?array $targetSections = null,
        string $taskType = 'update_content',
    ): StrategicTask {
        $st = StrategicTask::create([
            'id' => StrategicTask::generateId(),
            'clinic_id' => $site->clinic_id,
            'trigger_type' => 'free_input',
            'title' => $title,
            'description' => $description,
            'intent' => $description,
            'priority' => 'medium',
            'risk_level' => 'low',
            'target_channels' => ['web'],
            'status' => 'approved', // フリー入力は確認済みなので即承認
            'created_by' => 'human:' . auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ChannelTask::create([
            'id' => ChannelTask::generateId('web'),
            'strategic_task_id' => $st->id,
            'channel' => 'web',
            'task_type' => $taskType,
            'title' => $title,
            'instruction' => $description,
            'target_site_id' => $site->id,
            'target_page_id' => $targetPageId,
            'target_sections' => $targetSections,
            'status' => 'pending',
            'assigned_to' => 'ai',
        ]);

        return $st;
    }

    /**
     * 影響マップからチャネルタスクを生成する
     */
    private function generateChannelTasks(
        StrategicTask $st,
        Site $site,
        array $impactMap,
        array $changes,
    ): void {
        foreach (['high', 'medium', 'low'] as $level) {
            foreach ($impactMap[$level] as $impact) {
                $page = $impact['page'] ?? null;

                ChannelTask::create([
                    'id' => ChannelTask::generateId('web'),
                    'strategic_task_id' => $st->id,
                    'channel' => 'web',
                    'task_type' => $impact['task_type'],
                    'title' => $page
                        ? "{$page->title}の更新（{$impact['task_type']}）"
                        : "サイト全体の{$impact['task_type']}",
                    'instruction' => $impact['reason'],
                    'target_site_id' => $site->id,
                    'target_page_id' => $page?->id,
                    'input_data' => [
                        'dna_changes' => $changes,
                        'impact_level' => $level,
                    ],
                    'status' => 'pending',
                    'assigned_to' => 'ai',
                ]);
            }
        }
    }

    private function buildTitle(array $sheetNames, int $impactCount): string
    {
        $sheetsStr = implode('・', array_map(fn($s) => str_replace('_', ' ', $s), array_slice($sheetNames, 0, 3)));
        if (count($sheetNames) > 3) {
            $sheetsStr .= '等';
        }
        return "DNA-OS更新（{$sheetsStr}）→ Web反映（{$impactCount}件）";
    }

    private function buildDescription(array $changes, array $impactMap): string
    {
        $lines = ["DNA-OSで以下の変更が検出されました：\n"];
        foreach (array_slice($changes, 0, 5) as $c) {
            $sheet = $c['destination_sheet'] ?? '不明';
            $field = $c['destination_field'] ?? '不明';
            $lines[] = "- {$sheet} / {$field}";
        }
        if (count($changes) > 5) {
            $lines[] = "  ...他" . (count($changes) - 5) . "件";
        }
        $lines[] = "\n影響分析: 高={$this->count($impactMap, 'high')}, 中={$this->count($impactMap, 'medium')}, 低={$this->count($impactMap, 'low')}";
        return implode("\n", $lines);
    }

    private function count(array $map, string $key): int
    {
        return count($map[$key] ?? []);
    }
}
