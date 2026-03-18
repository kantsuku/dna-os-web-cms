<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRecord;
use App\Models\ChannelTask;
use App\Models\OrchestrationLog;
use App\Models\StrategicTask;
use Illuminate\Http\Request;

class StrategicTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = StrategicTask::query()
            ->with('channelTasks')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($clinicId = $request->input('clinic_id')) {
            $query->where('clinic_id', $clinicId);
        }

        $tasks = $query->paginate(20);
        $pendingCount = StrategicTask::pending()->count();

        return view('strategy.tasks.index', compact('tasks', 'pendingCount'));
    }

    public function show(StrategicTask $strategicTask)
    {
        $strategicTask->load([
            'channelTasks.targetPage',
            'channelTasks.targetSite',
            'approver',
            'approvalRecords.approver',
        ]);

        return view('strategy.tasks.show', compact('strategicTask'));
    }

    public function approve(StrategicTask $strategicTask)
    {
        if (!$strategicTask->canBeApproved()) {
            return redirect()->back()->with('error', 'このタスクは承認できません');
        }

        $strategicTask->approve(auth()->id());

        ApprovalRecord::recordApproval(
            'strategic_task',
            $strategicTask->id,
            auth()->id(),
        );

        OrchestrationLog::log(
            $strategicTask->clinic_id,
            'approval_requested',
            'strategic_task',
            $strategicTask->id,
            ['action' => 'approved', 'by' => auth()->id()],
        );

        return redirect()->back()->with('success', '戦略タスクを承認しました');
    }

    public function reject(Request $request, StrategicTask $strategicTask)
    {
        $request->validate(['comment' => 'required|string']);

        $strategicTask->cancel();

        ApprovalRecord::recordRejection(
            'strategic_task',
            $strategicTask->id,
            auth()->id(),
            $request->input('comment'),
        );

        return redirect()->back()->with('success', '戦略タスクを却下しました');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['task_ids' => 'required|array']);

        $tasks = StrategicTask::whereIn('id', $request->input('task_ids'))
            ->where('status', 'pending_approval')
            ->where('risk_level', 'low')
            ->get();

        foreach ($tasks as $task) {
            $task->approve(auth()->id());
            ApprovalRecord::recordApproval('strategic_task', $task->id, auth()->id());
        }

        return redirect()->back()->with('success', $tasks->count() . '件のタスクを一括承認しました');
    }
}
