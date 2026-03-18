<?php

namespace App\Http\Controllers;

use App\Models\DeployRecord;
use App\Models\Site;
use App\Services\FtpDeployService;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function index(Site $site)
    {
        $readyPages = $site->pages()
            ->where('status', 'ready')
            ->with('currentGeneration')
            ->get();

        $history = $site->deployRecords()
            ->with('deployer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('publish.index', compact('site', 'readyPages', 'history'));
    }

    public function deploy(Request $request, Site $site, SiteBuildService $buildService, FtpDeployService $ftpService)
    {
        $pageIds = $request->input('page_ids', []);

        // 世代スナップショット
        $snapshot = [];
        $pages = $site->pages()->whereIn('id', $pageIds)->with('currentGeneration')->get();
        foreach ($pages as $page) {
            if ($page->currentGeneration) {
                $snapshot[$page->id] = $page->current_generation_id;
            }
        }

        // ビルド
        $buildPath = $buildService->buildSite($site);

        $record = DeployRecord::create([
            'site_id' => $site->id,
            'generation_snapshot' => $snapshot,
            'build_path' => $buildPath,
            'deploy_status' => 'building',
            'deployed_by' => auth()->id(),
        ]);

        $success = $ftpService->deploy($site, $buildPath, $record);

        if ($success) {
            // ページと世代のステータスを published に
            foreach ($pages as $page) {
                $page->update(['status' => 'published']);
                $page->currentGeneration?->update(['status' => 'published']);
            }

            return redirect()->route('sites.publish.index', $site)->with('success', 'デプロイ完了');
        }

        return redirect()->route('sites.publish.index', $site)->with('error', 'デプロイ失敗: ' . $record->error_log);
    }

    public function rollback(Site $site, DeployRecord $record, FtpDeployService $ftpService)
    {
        $rollbackRecord = $ftpService->rollback($site, $record);

        return redirect()->route('sites.publish.index', $site)->with(
            $rollbackRecord->deploy_status === 'success' ? 'success' : 'error',
            $rollbackRecord->deploy_status === 'success' ? 'ロールバック完了' : 'ロールバック失敗',
        );
    }
}
