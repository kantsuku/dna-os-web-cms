<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Site;
use App\Models\SiteDesign;
use App\Services\FtpDeployService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Clinic $clinic)
    {
        $sites = $clinic->sites()->withCount('pages')->orderBy('name')->get();
        return view('sites.index', compact('clinic', 'sites'));
    }

    public function create(Clinic $clinic)
    {
        return view('sites.create', compact('clinic'));
    }

    public function store(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', 'in:hp,specialty,recruitment,lp,other'],
            'site_label' => ['nullable', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'xserver_host' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_user' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_pass' => ['nullable', 'string'],
            'xserver_deploy_path' => ['nullable', 'string', 'max:500'],
            'gas_generator_url' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['clinic_id'] = $clinic->clinic_id;
        $validated['clinic_ref_id'] = $clinic->id;

        $site = Site::create($validated);

        $design = SiteDesign::create([
            'site_id' => $site->id,
            'name' => 'default',
            'status' => 'active',
        ]);
        $site->update(['design_id' => $design->id]);

        return redirect()->route('clinic.sites.show', [$clinic, $site])->with('success', 'サイトを作成しました');
    }

    public function show(Clinic $clinic, Site $site)
    {
        $site->load([
            'pages' => fn ($q) => $q->with('currentGeneration')->orderBy('sort_order'),
            'deployRecords' => fn ($q) => $q->latest()->limit(5),
        ]);
        return view('sites.show', compact('clinic', 'site'));
    }

    public function edit(Clinic $clinic, Site $site)
    {
        return view('sites.edit', compact('clinic', 'site'));
    }

    public function update(Request $request, Clinic $clinic, Site $site)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_type' => ['required', 'in:hp,specialty,recruitment,lp,other'],
            'site_label' => ['nullable', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'xserver_host' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_user' => ['nullable', 'string', 'max:255'],
            'xserver_ftp_pass' => ['nullable', 'string'],
            'xserver_deploy_path' => ['nullable', 'string', 'max:500'],
            'gas_generator_url' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,maintenance,archived'],
        ]);

        $site->update($validated);
        return redirect()->route('clinic.sites.show', [$clinic, $site])->with('success', 'サイトを更新しました');
    }

    public function testFtp(Clinic $clinic, Site $site, FtpDeployService $ftpService)
    {
        $ok = $ftpService->testConnection($site);
        return redirect()->route('clinic.sites.show', [$clinic, $site])->with($ok ? 'success' : 'error', $ok ? 'FTP接続成功' : 'FTP接続失敗');
    }
}
