<?php

namespace App\Http\Controllers;

use App\Models\ContentVariant;
use App\Models\PublishRecord;
use App\Models\Site;

class DashboardController extends Controller
{
    public function index()
    {
        $sites = Site::where('status', 'active')->withCount('pages')->get();
        $pendingReviews = ContentVariant::where('status', 'pending_review')->count();
        $recentPublishes = PublishRecord::with('site')
            ->where('deploy_status', 'success')
            ->orderByDesc('deployed_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('sites', 'pendingReviews', 'recentPublishes'));
    }
}
