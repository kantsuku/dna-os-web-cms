<?php

namespace App\Http\Controllers;

use App\Models\ChannelTask;
use App\Models\Clinic;
use App\Models\ExceptionContent;
use App\Models\PageGeneration;
use App\Models\StrategicTask;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * 医院選択画面（admin: 全医院、その他: 自分の医院）
     */
    public function select()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $clinics = Clinic::where('status', 'active')
                ->withCount('sites')
                ->get();
        } else {
            $clinics = $user->clinics()
                ->where('status', 'active')
                ->withCount('sites')
                ->get();
        }

        // 医院が1つだけの場合は直接ダッシュボードへ
        if ($clinics->count() === 1) {
            return redirect()->route('clinic.dashboard', $clinics->first());
        }

        return view('clinics.select', compact('clinics'));
    }

    /**
     * 医院ダッシュボード（作戦本部）
     */
    public function dashboard(Clinic $clinic)
    {
        $clinic->load(['sites' => fn($q) => $q->withCount('pages'), 'design']);

        // KPIサマリー
        $siteIds = $clinic->sites->pluck('id');
        $totalPages = $clinic->sites->sum('pages_count');
        $newGenerations = PageGeneration::whereHas('page', fn($q) => $q->whereIn('site_id', $siteIds))
            ->whereIn('status', ['draft', 'received', 'ready'])->count();

        // 戦略タスク
        $pendingTasks = StrategicTask::where('clinic_id', $clinic->clinic_id)
            ->where('status', 'pending_approval')->count();
        $activeTasks = StrategicTask::where('clinic_id', $clinic->clinic_id)
            ->whereIn('status', ['approved', 'in_progress'])->count();
        $recentTasks = StrategicTask::where('clinic_id', $clinic->clinic_id)
            ->orderByDesc('created_at')->limit(5)->get();

        // 承認待ち
        $reviewReadyTasks = ChannelTask::whereIn('target_site_id', $siteIds)
            ->where('status', 'review_ready')->count();
        $pendingExceptions = ExceptionContent::whereHas('page', fn($q) => $q->whereIn('site_id', $siteIds))
            ->whereIn('status', ['first_review', 'final_review'])->count();
        $totalPendingApprovals = $pendingTasks + $reviewReadyTasks + $pendingExceptions;

        return view('clinics.dashboard', compact(
            'clinic', 'totalPages', 'newGenerations',
            'pendingTasks', 'activeTasks', 'recentTasks',
            'totalPendingApprovals', 'reviewReadyTasks', 'pendingExceptions',
        ));
    }

    /**
     * 医院作成
     */
    public function create()
    {
        return view('clinics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|string|max:50|unique:clinics,clinic_id',
            'name' => 'required|string|max:255',
            'gas_webapp_url' => 'nullable|url',
        ]);

        $clinic = Clinic::create($validated);
        $clinic->users()->attach(auth()->id());

        return redirect()->route('clinic.dashboard', $clinic)->with('success', '医院を作成しました');
    }
}
