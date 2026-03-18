<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\OrchestrationLog;
use App\Models\Site;
use App\Services\Strategy\DnaOsSyncService;
use App\Services\Strategy\TaskGenerationService;
use Illuminate\Http\Request;

class DnaOsUpdateController extends Controller
{
    public function index(Clinic $clinic)
    {
        $updates = OrchestrationLog::where('event_type', 'dna_change_detected')
            ->orderByDesc('created_at')
            ->paginate(20);

        $sites = Site::where('status', 'active')->whereNotNull('gas_generator_url')->get();

        return view('strategy.dna-updates.index', compact('updates', 'sites'));
    }

    /**
     * 手動同期トリガー
     */
    public function sync(Clinic $clinic, Request $request, DnaOsSyncService $syncService, TaskGenerationService $taskService)
    {
        $request->validate(['site_id' => 'required|exists:sites,id']);
        $site = Site::findOrFail($request->input('site_id'));

        $changes = $syncService->fetchRecentChanges($site);

        if (empty($changes)) {
            return redirect()->back()->with('success', '新しい変更はありませんでした');
        }

        $st = $taskService->generateFromDnaChanges($site, $changes);

        if ($st) {
            return redirect()->route('clinic.strategy.tasks.show', [$clinic, $st])
                ->with('success', count($changes) . '件の変更を検出し、タスクを生成しました');
        }

        return redirect()->back()->with('success', '変更を検出しましたが、Web影響なしと判定されました');
    }
}
