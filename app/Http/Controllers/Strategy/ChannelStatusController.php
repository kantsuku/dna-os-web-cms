<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\ChannelTask;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ChannelStatusController extends Controller
{
    public function index(Clinic $clinic, Request $request)
    {
        $query = ChannelTask::with(['strategicTask', 'targetSite', 'targetPage'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($siteId = $request->input('site_id')) {
            $query->forSite($siteId);
        }

        $tasks = $query->paginate(20);

        // ステータス内訳
        $statusCounts = ChannelTask::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('strategy.channel-status.index', compact('tasks', 'statusCounts'));
    }
}
