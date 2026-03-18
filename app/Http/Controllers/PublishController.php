<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\DeployRecord;
use App\Models\Site;
use App\Services\FtpDeployService;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function index(Clinic $clinic, Site $site)
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

    public function deploy(Request $request, Clinic $clinic, Site $site, SiteBuildService $buildService, FtpDeployService $ftpService)
    {
        $pageIds = $request->input('page_ids', []);

        $snapshot = [];
        $pages = $site->pages()->whereIn('id', $pageIds)->with('currentGeneration')->get();
        foreach ($pages as $page) {
            if ($page->currentGeneration) {
                $snapshot[$page->id] = $page->current_generation_id;
            }
        }

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
            foreach ($pages as $page) {
                $page->update(['status' => 'published']);
                $page->currentGeneration?->update(['status' => 'published']);
            }
            return redirect()->route('clinic.sites.publish.index', [$clinic, $site])->with('success', 'デプロイ完了');
        }

        return redirect()->route('clinic.sites.publish.index', [$clinic, $site])->with('error', 'デプロイ失敗: ' . $record->error_log);
    }

    public function rollback(Clinic $clinic, Site $site, DeployRecord $record, FtpDeployService $ftpService)
    {
        $rollbackRecord = $ftpService->rollback($site, $record);

        return redirect()->route('clinic.sites.publish.index', [$clinic, $site])->with(
            $rollbackRecord->deploy_status === 'success' ? 'success' : 'error',
            $rollbackRecord->deploy_status === 'success' ? 'ロールバック完了' : 'ロールバック失敗',
        );
    }
}
