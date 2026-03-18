<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\FreeInputRequest;
use App\Models\Site;
use Illuminate\Http\Request;

class FreeInputController extends Controller
{
    public function index()
    {
        $requests = FreeInputRequest::with(['site', 'submitter', 'strategicTask'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $sites = Site::where('status', 'active')->get();

        return view('strategy.free-input.index', compact('requests', 'sites'));
    }

    public function store(Request $request)
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

        // AI解釈はPhase 3で実装。現時点ではpendingのまま保存
        return redirect()->route('strategy.free-input.show', $freeInput)
            ->with('success', '修正依頼を送信しました');
    }

    public function show(FreeInputRequest $freeInputRequest)
    {
        $freeInputRequest->load(['site', 'submitter', 'strategicTask']);

        return view('strategy.free-input.show', compact('freeInputRequest'));
    }

    public function confirm(FreeInputRequest $freeInputRequest)
    {
        if ($freeInputRequest->interpretation_status !== 'interpreted') {
            return redirect()->back()->with('error', 'AI解釈がまだ完了していません');
        }

        $freeInputRequest->update(['interpretation_status' => 'confirmed']);

        // タスク生成はPhase 3で実装
        return redirect()->back()->with('success', '解釈を確認しました');
    }
}
