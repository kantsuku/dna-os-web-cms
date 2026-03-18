<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\FreeInputRequest;
use App\Models\Site;
use App\Services\Strategy\AiInterpretationService;
use App\Services\Strategy\TaskGenerationService;
use Illuminate\Http\Request;

class FreeInputController extends Controller
{
    public function index(Clinic $clinic)
    {
        $requests = FreeInputRequest::with(['site', 'submitter', 'strategicTask'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $sites = Site::where('status', 'active')->get();

        return view('strategy.free-input.index', compact('requests', 'sites'));
    }

    public function store(Clinic $clinic, Request $request, AiInterpretationService $aiService)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'raw_text' => 'required|string|max:5000',
        ]);

        $site = Site::findOrFail($validated['site_id']);

        $freeInput = FreeInputRequest::create([
            'clinic_id' => $site->clinic_id,
            'site_id' => $site->id,
            'raw_text' => $validated['raw_text'],
            'interpretation_status' => 'pending',
            'submitted_by' => auth()->id(),
        ]);

        // AI解釈を実行
        $aiService->interpretAndSave($freeInput);

        return redirect()->route('clinic.strategy.free-input.show', [$clinic, $freeInput])
            ->with('success', '修正依頼を送信し、AI解釈を実行しました');
    }

    public function show(Clinic $clinic, FreeInputRequest $freeInputRequest)
    {
        $freeInputRequest->load(['site', 'submitter', 'strategicTask.channelTasks']);

        return view('strategy.free-input.show', compact('freeInputRequest'));
    }

    public function confirm(Clinic $clinic, FreeInputRequest $freeInputRequest, TaskGenerationService $taskService)
    {
        if ($freeInputRequest->interpretation_status !== 'interpreted') {
            return redirect()->back()->with('error', 'AI解釈がまだ完了していません');
        }

        $site = $freeInputRequest->site;
        $interp = $freeInputRequest->ai_interpretation ?? [];

        // 対象ページを特定
        $targetPageId = null;
        if (!empty($interp['target_page_slug'])) {
            $targetPage = $site->pages()->where('slug', $interp['target_page_slug'])->first();
            $targetPageId = $targetPage?->id;
        }

        // タスク生成
        $st = $taskService->generateFromFreeInput(
            $site,
            $interp['description'] ?? $freeInputRequest->raw_text,
            $freeInputRequest->raw_text,
            $targetPageId,
            $interp['target_section'] ? [$interp['target_section']] : null,
            $interp['task_type'] ?? 'update_content',
        );

        $freeInputRequest->update([
            'interpretation_status' => 'confirmed',
            'strategic_task_id' => $st->id,
        ]);

        return redirect()->route('clinic.strategy.tasks.show', [$clinic, $st])
            ->with('success', '解釈を確認し、タスクを生成しました');
    }

    public function reject(Clinic $clinic, FreeInputRequest $freeInputRequest)
    {
        $freeInputRequest->update(['interpretation_status' => 'rejected']);
        return redirect()->route('clinic.strategy.free-input.index', $clinic)
            ->with('success', '修正依頼を却下しました');
    }
}
