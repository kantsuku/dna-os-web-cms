<?php

namespace App\Services;

use App\Models\ContentVariant;
use App\Models\Section;
use App\Models\Site;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DnaOsSyncService
{
    /**
     * 1サイト分の同期を実行
     */
    public function syncSite(Site $site): SyncLog
    {
        $log = SyncLog::create([
            'site_id' => $site->id,
            'sync_type' => 'manual',
            'status' => 'success',
            'started_at' => now(),
        ]);

        $updated = 0;
        $skipped = 0;
        $conflicted = 0;
        $details = [];

        try {
            $remoteData = $this->fetchFromDnaOs($site->clinic_id);

            $sections = Section::whereHas('page', fn ($q) => $q->where('site_id', $site->id))
                ->where('content_source_type', 'dna_os')
                ->with(['overrideRule', 'variants'])
                ->get();

            foreach ($sections as $section) {
                $result = $this->syncSection($section, $remoteData);
                $details[] = $result;

                match ($result['action']) {
                    'updated' => $updated++,
                    'skipped' => $skipped++,
                    'conflict' => $conflicted++,
                    default => null,
                };
            }

            $status = $conflicted > 0 ? 'partial' : 'success';
        } catch (\Throwable $e) {
            $status = 'failed';
            $details[] = ['error' => $e->getMessage()];
            Log::error('DNA-OS同期エラー', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
        }

        $log->update([
            'sections_updated' => $updated,
            'sections_skipped' => $skipped,
            'sections_conflicted' => $conflicted,
            'details' => $details,
            'status' => $status,
            'completed_at' => now(),
        ]);

        return $log;
    }

    /**
     * DNA-OS GAS WebApp からデータ取得
     */
    private function fetchFromDnaOs(string $clinicId): array
    {
        $webAppUrl = config('services.dna_os.webapp_url');

        if (empty($webAppUrl)) {
            Log::warning('DNA-OS WebApp URLが未設定です');
            return [];
        }

        $response = Http::timeout(30)->get($webAppUrl, [
            'action' => 'get_clinic_data',
            'clinic_id' => $clinicId,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('DNA-OS APIエラー: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * セクション単位の同期
     */
    private function syncSection(Section $section, array $remoteData): array
    {
        $ref = $section->content_source_ref;
        if (empty($ref)) {
            return ['section_id' => $section->id, 'action' => 'skipped', 'reason' => '参照なし'];
        }

        $remoteContent = $this->extractContent($remoteData, $ref);
        if ($remoteContent === null) {
            return ['section_id' => $section->id, 'action' => 'skipped', 'reason' => 'リモートにデータなし'];
        }

        $currentVariant = $section->variants()->orderByDesc('version')->first();

        // 変更なしチェック
        if ($currentVariant && $currentVariant->original_content === $remoteContent) {
            return ['section_id' => $section->id, 'action' => 'skipped', 'reason' => '変更なし'];
        }

        $policy = $section->override_policy;

        return match ($policy) {
            'auto_sync' => $this->autoSync($section, $remoteContent, $currentVariant),
            'confirm_before_sync' => $this->confirmBeforeSync($section, $remoteContent),
            'locked', 'manual_only' => [
                'section_id' => $section->id,
                'action' => 'skipped',
                'reason' => "ポリシー: {$policy}",
            ],
        };
    }

    private function autoSync(Section $section, string $remoteContent, ?ContentVariant $currentVariant): array
    {
        // 既存バリアントを superseded に
        if ($currentVariant && $currentVariant->status !== 'superseded') {
            $currentVariant->update(['status' => 'superseded']);
        }

        $nextVersion = ($currentVariant?->version ?? 0) + 1;

        ContentVariant::create([
            'section_id' => $section->id,
            'version' => $nextVersion,
            'source_type' => 'dna_os_sync',
            'content_html' => $remoteContent,
            'content_raw' => strip_tags($remoteContent),
            'original_content' => $remoteContent,
            'status' => 'draft',
        ]);

        return ['section_id' => $section->id, 'action' => 'updated', 'version' => $nextVersion];
    }

    private function confirmBeforeSync(Section $section, string $remoteContent): array
    {
        $nextVersion = ($section->variants()->max('version') ?? 0) + 1;

        // 候補バリアントとして作成（既存は維持）
        ContentVariant::create([
            'section_id' => $section->id,
            'version' => $nextVersion,
            'source_type' => 'dna_os_sync',
            'content_html' => $remoteContent,
            'content_raw' => strip_tags($remoteContent),
            'original_content' => $remoteContent,
            'status' => 'draft',
        ]);

        return ['section_id' => $section->id, 'action' => 'conflict', 'reason' => '要確認'];
    }

    /**
     * リモートデータから参照キーに対応するコンテンツを抽出
     */
    private function extractContent(array $remoteData, array $ref): ?string
    {
        $sheet = $ref['sheet'] ?? null;
        $recordId = $ref['record_id'] ?? null;
        $field = $ref['field'] ?? 'content';

        if (!$sheet || !$recordId) {
            return null;
        }

        return $remoteData[$sheet][$recordId][$field] ?? null;
    }
}
