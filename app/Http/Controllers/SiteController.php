<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\DnaOsSyncService;
use App\Services\FtpDeployService;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withCount('pages')
            ->orderBy('name')
            ->get();

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'xserver_host' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_user' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_pass' => ['nullable', 'string'],
            'xserver_deploy_path' => ['nullable', 'string', 'max:500'],
            'template_set' => ['nullable', 'string', 'max:100'],
        ]);

        $site = Site::create($validated);

        return redirect()->route('sites.show', $site)->with('success', 'サイトを作成しました');
    }

    public function show(Site $site)
    {
        $site->load(['pages' => fn ($q) => $q->orderBy('sort_order'), 'syncLogs' => fn ($q) => $q->latest()->limit(5)]);
        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'clinic_id' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'xserver_host' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_user' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_pass' => ['nullable', 'string'],
            'xserver_deploy_path' => ['nullable', 'string', 'max:500'],
            'template_set' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,maintenance,archived'],
        ]);

        $site->update($validated);

        return redirect()->route('sites.show', $site)->with('success', 'サイトを更新しました');
    }

    public function sync(Site $site, DnaOsSyncService $syncService)
    {
        $log = $syncService->syncSite($site);

        $message = match ($log->status) {
            'success' => "同期完了: {$log->sections_updated}件更新",
            'partial' => "同期一部完了: {$log->sections_updated}件更新, {$log->sections_conflicted}件要確認",
            'failed' => '同期失敗',
        };

        return redirect()->route('sites.show', $site)->with(
            $log->status === 'failed' ? 'error' : 'success',
            $message,
        );
    }

    public function testFtp(Site $site, FtpDeployService $ftpService)
    {
        $ok = $ftpService->testConnection($site);

        return redirect()->route('sites.show', $site)->with(
            $ok ? 'success' : 'error',
            $ok ? 'FTP接続成功' : 'FTP接続失敗',
        );
    }
}
