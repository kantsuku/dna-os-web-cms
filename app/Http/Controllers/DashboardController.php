<?php

namespace App\Http\Controllers;

use App\Models\ChannelTask;
use App\Models\DeployRecord;
use App\Models\ExceptionContent;
use App\Models\PageGeneration;
use App\Models\Site;
use App\Models\StrategicTask;

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

        // v3: 戦略タスク概要
        $pendingStrategicTasks = StrategicTask::pending()->count();
        $activeStrategicTasks = StrategicTask::active()->count();
        $reviewReadyChannelTasks = ChannelTask::reviewReady()->count();
        $pendingExceptions = ExceptionContent::whereIn('status', ['first_review', 'final_review'])->count();

        // 承認待ち合計
        $totalPendingApprovals = $pendingStrategicTasks + $reviewReadyChannelTasks + $pendingExceptions;

        return view('dashboard', compact(
            'sites', 'newGenerations', 'recentDeploys',
            'pendingStrategicTasks', 'activeStrategicTasks',
            'reviewReadyChannelTasks', 'totalPendingApprovals',
        ));
    }
}
