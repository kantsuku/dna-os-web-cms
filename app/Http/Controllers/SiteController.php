<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteDesign;
use App\Services\FtpDeployService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withCount('pages')->orderBy('name')->get();
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
            'gas_generator_url' => ['nullable', 'string', 'max:500'],
        ]);

        $site = Site::create($validated);

        // デフォルトデザインを作成
        $design = SiteDesign::create([
            'site_id' => $site->id,
            'name' => 'default',
            'status' => 'active',
        ]);
        $site->update(['design_id' => $design->id]);

        return redirect()->route('sites.show', $site)->with('success', 'サイトを作成しました');
    }

    public function show(Site $site)
    {
        $site->load(['pages' => fn ($q) => $q->with('currentGeneration')->orderBy('sort_order'), 'deployRecords' => fn ($q) => $q->latest()->limit(5)]);
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
            'gas_generator_url' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,maintenance,archived'],
        ]);

        $site->update($validated);
        return redirect()->route('sites.show', $site)->with('success', 'サイトを更新しました');
    }

    public function testFtp(Site $site, FtpDeployService $ftpService)
    {
        $ok = $ftpService->testConnection($site);
        return redirect()->route('sites.show', $site)->with($ok ? 'success' : 'error', $ok ? 'FTP接続成功' : 'FTP接続失敗');
    }
}
