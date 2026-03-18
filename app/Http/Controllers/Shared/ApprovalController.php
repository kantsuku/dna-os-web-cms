<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRecord;
use App\Models\ChannelTask;
use App\Models\ExceptionContent;
use App\Models\StrategicTask;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        // 承認待ちを全種別横断で取得
        $strategicTasks = StrategicTask::where('status', 'pending_approval')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get()
            ->map(fn ($t) => [
                'type' => 'strategic_task',
                'id' => $t->id,
                'title' => $t->title,
                'priority' => $t->priority,
                'risk_level' => $t->risk_level,
                'created_at' => $t->created_at,
                'model' => $t,
            ]);

        $channelTasks = ChannelTask::where('status', 'review_ready')
            ->with(['targetSite', 'targetPage'])
            ->get()
            ->map(fn ($t) => [
                'type' => 'channel_task',
                'id' => $t->id,
                'title' => $t->title,
                'priority' => $t->strategicTask?->priority ?? 'medium',
                'risk_level' => $t->strategicTask?->risk_level ?? 'medium',
                'created_at' => $t->created_at,
                'model' => $t,
            ]);

        $exceptions = ExceptionContent::whereIn('status', ['first_review', 'final_review'])
            ->with('page.site')
            ->get()
            ->map(fn ($e) => [
                'type' => 'exception_content',
                'id' => (string) $e->id,
                'title' => $e->title,
                'priority' => 'high',
                'risk_level' => 'high',
                'created_at' => $e->created_at,
                'model' => $e,
            ]);

        $pendingItems = $strategicTasks
            ->concat($channelTasks)
            ->concat($exceptions)
            ->sortByDesc('created_at');

        return view('shared.approvals.index', compact('pendingItems'));
    }

    public function history(Request $request)
    {
        $records = ApprovalRecord::with('approver')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('shared.approvals.history', compact('records'));
    }
}
