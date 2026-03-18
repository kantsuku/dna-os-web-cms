<?php

namespace App\Http\Controllers;

use App\Models\DeployRecord;
use App\Models\PageGeneration;
use App\Models\Site;

class DashboardController extends Controller
{
    public function index()
    {
        $sites = Site::where('status', 'active')->withCount('pages')->get();
        $newGenerations = PageGeneration::whereIn('status', ['received', 'ready'])->count();
        $recentDeploys = DeployRecord::with('site')
            ->where('deploy_status', 'success')
            ->orderByDesc('deployed_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('sites', 'newGenerations', 'recentDeploys'));
    }
}
