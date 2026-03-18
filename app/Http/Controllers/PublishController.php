<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PublishRecord;
use App\Models\Site;
use App\Services\FtpDeployService;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function index(Site $site)
    {
        $approvedPages = $site->pages()
            ->whereIn('status', ['approved', 'published'])
            ->withCount('sections')
            ->get();

        $history = $site->publishRecords()
            ->with('deployer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('publish.index', compact('site', 'approvedPages', 'history'));
    }

    public function deploy(Request $request, Site $site, SiteBuildService $buildService, FtpDeployService $ftpService)
    {
        $pageIds = $request->input('page_ids', []);

        // ビルド
        $buildPath = $buildService->buildSite($site);

        // 公開レコード作成
        $record = PublishRecord::create([
            'site_id' => $site->id,
            'pages_json' => $pageIds,
            'snapshot_path' => $buildPath,
            'deploy_status' => 'building',
            'deployed_by' => auth()->id(),
        ]);

        // FTPデプロイ
        $success = $ftpService->deploy($site, $buildPath, $record);

        if ($success) {
            // ページのステータスを published に更新
            $site->pages()->whereIn('id', $pageIds)->update(['status' => 'published']);

            return redirect()->route('sites.publish.index', $site)->with('success', 'デプロイ完了');
        }

        return redirect()->route('sites.publish.index', $site)->with('error', 'デプロイ失敗: ' . $record->error_log);
    }

    public function rollback(Site $site, PublishRecord $record, FtpDeployService $ftpService)
    {
        $rollbackRecord = $ftpService->rollback($site, $record);

        $message = $rollbackRecord->deploy_status === 'success'
            ? 'ロールバック完了'
            : 'ロールバック失敗';

        return redirect()->route('sites.publish.index', $site)->with(
            $rollbackRecord->deploy_status === 'success' ? 'success' : 'error',
            $message,
        );
    }
}
