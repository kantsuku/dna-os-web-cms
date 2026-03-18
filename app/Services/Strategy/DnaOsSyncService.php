<?php

namespace App\Services\Strategy;

use App\Models\OrchestrationLog;
use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DnaOsSyncService
{
    /**
     * DNA-OSから最近の変更を取得する
     *
     * GAS API: GET /exec?action=getRecentReflections&since=ISO8601&clinic_id=XXX
     * → [{proposal_id, destination_sheet, destination_field, proposed_value, reflected_at}]
     */
    public function fetchRecentChanges(Site $site, ?\DateTimeInterface $since = null): array
    {
        if (!$site->gas_generator_url) {
            return [];
        }

        $since = $since ?? now()->subHours(6);

        $baseUrl = $this->extractGasBaseUrl($site->gas_generator_url);
        if (!$baseUrl) {
            return [];
        }

        try {
            $response = Http::timeout(30)->get($baseUrl, [
                'action' => 'getRecentReflections',
                'since' => $since->toIso8601String(),
                'clinic_id' => $site->clinic_id,
            ]);

            if ($response->failed()) {
                Log::warning('DNA-OS同期失敗', [
                    'site_id' => $site->id,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [];
            }

            // 変更をOrchestrationLogに記録
            foreach ($data as $change) {
                OrchestrationLog::log(
                    $site->clinic_id,
                    'dna_change_detected',
                    'dna_os_proposal',
                    $change['proposal_id'] ?? null,
                    $change,
                );
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('DNA-OS同期エラー', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * 全アクティブサイトに対してポーリングを実行する
     */
    public function pollAllSites(): array
    {
        $results = [];
        $sites = Site::where('status', 'active')
            ->whereNotNull('gas_generator_url')
            ->get();

        foreach ($sites as $site) {
            $lastCheck = OrchestrationLog::where('clinic_id', $site->clinic_id)
                ->where('event_type', 'dna_change_detected')
                ->latest('created_at')
                ->value('created_at');

            $changes = $this->fetchRecentChanges($site, $lastCheck);
            if (!empty($changes)) {
                $results[$site->clinic_id] = $changes;
            }
        }

        return $results;
    }

    /**
     * GAS URLからベースURLを抽出
     */
    private function extractGasBaseUrl(string $url): ?string
    {
        // GAS WebApp URL: https://script.google.com/macros/s/XXXXX/exec
        if (preg_match('#(https://script\.google\.com/macros/s/[^/]+/exec)#', $url, $m)) {
            return $m[1];
        }
        return $url;
    }
}
