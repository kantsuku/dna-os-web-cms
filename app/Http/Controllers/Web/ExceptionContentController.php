<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRecord;
use App\Models\Clinic;
use App\Models\ExceptionContent;
use App\Models\Page;
use App\Models\Site;
use App\Services\Web\ComplianceCheckService;
use Illuminate\Http\Request;

class ExceptionContentController extends Controller
{
    public function index(Clinic $clinic, Site $site)
    {
        $exceptions = ExceptionContent::whereHas('page', fn($q) => $q->where('site_id', $site->id))
            ->with('page')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('exceptions.index', compact('site', 'exceptions'));
    }

    public function create(Clinic $clinic, Site $site)
    {
        $pages = $site->pages()->whereIn('page_type', ['case', 'exception'])->get();
        return view('exceptions.create', compact('site', 'pages'));
    }

    public function store(Request $request, Clinic $clinic, Site $site, ComplianceCheckService $complianceService)
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'content_type' => 'required|in:case_study,case,medical_ad_gl,effect_claim,compliance_text,other',
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'structured_data' => 'nullable|array',
            'structured_data.chief_complaint' => 'nullable|string',
            'structured_data.treatment' => 'nullable|string',
            'structured_data.duration' => 'nullable|string',
            'structured_data.cost' => 'nullable|string',
            'structured_data.risks' => 'nullable|string',
            'structured_data.age_gender' => 'nullable|string',
            'compliance_notes' => 'nullable|string',
        ]);

        $exception = ExceptionContent::create([
            ...$validated,
            'status' => 'draft',
            'visibility' => 'private',
        ]);

        // コンプライアンスチェック実行
        $complianceService->check($exception);

        return redirect()->route('sites.exceptions.show', [$site, $exception])
            ->with('success', '例外コンテンツを作成し、コンプライアンスチェックを実行しました');
    }

    public function show(Clinic $clinic, Site $site, ExceptionContent $exception)
    {
        $exception->load(['page', 'firstApprover', 'finalApprover', 'approvalRecords.approver']);
        $complianceResults = $exception->compliance_check ?? [];

        return view('exceptions.show', compact('site', 'exception', 'complianceResults'));
    }

    public function edit(Clinic $clinic, Site $site, ExceptionContent $exception)
    {
        $pages = $site->pages()->whereIn('page_type', ['case', 'exception'])->get();
        return view('exceptions.edit', compact('site', 'exception', 'pages'));
    }

    public function update(Request $request, Clinic $clinic, Site $site, ExceptionContent $exception, ComplianceCheckService $complianceService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'structured_data' => 'nullable|array',
            'compliance_notes' => 'nullable|string',
        ]);

        $exception->update($validated);

        // コンプライアンスチェック再実行
        $complianceService->check($exception);

        return redirect()->route('sites.exceptions.show', [$site, $exception])
            ->with('success', '例外コンテンツを更新しました');
    }

    /**
     * 一次承認（管理者）
     */
    public function firstApprove(Clinic $clinic, Site $site, ExceptionContent $exception)
    {
        if ($exception->status !== 'first_review') {
            return redirect()->back()->with('error', '一次承認待ち状態ではありません');
        }

        $exception->update([
            'status' => 'final_review',
            'first_approved_by' => auth()->id(),
            'first_approved_at' => now(),
        ]);

        ApprovalRecord::recordApproval(
            'exception_content', (string) $exception->id, auth()->id(), 'first_review'
        );

        return redirect()->back()->with('success', '一次承認しました。最終承認をお待ちください。');
    }

    /**
     * 最終承認（院長/法務）
     */
    public function finalApprove(Clinic $clinic, Site $site, ExceptionContent $exception)
    {
        if ($exception->status !== 'final_review') {
            return redirect()->back()->with('error', '最終承認待ち状態ではありません');
        }

        $exception->update([
            'status' => 'approved',
            'final_approved_by' => auth()->id(),
            'final_approved_at' => now(),
        ]);

        ApprovalRecord::recordApproval(
            'exception_content', (string) $exception->id, auth()->id(), 'final_review'
        );

        return redirect()->back()->with('success', '最終承認しました。公開可能です。');
    }

    /**
     * 公開申請（draft → first_review）
     */
    public function submitForReview(Clinic $clinic, Site $site, ExceptionContent $exception, ComplianceCheckService $complianceService)
    {
        if ($exception->status !== 'draft') {
            return redirect()->back()->with('error', '下書き状態でのみ公開申請できます');
        }

        // コンプライアンスチェック再実行
        $results = $complianceService->check($exception);
        if ($complianceService->hasErrors($results)) {
            return redirect()->back()->with('error', 'コンプライアンスチェックでNGがあります。修正してから再申請してください。');
        }

        $exception->update(['status' => 'first_review']);

        return redirect()->back()->with('success', '公開申請しました。一次承認をお待ちください。');
    }

    /**
     * 却下
     */
    public function reject(Request $request, Clinic $clinic, Site $site, ExceptionContent $exception)
    {
        $request->validate(['comment' => 'required|string']);

        $currentLevel = $exception->status === 'first_review' ? 'first_review' : 'final_review';
        $exception->update(['status' => 'rejected']);

        ApprovalRecord::recordRejection(
            'exception_content', (string) $exception->id, auth()->id(),
            $request->input('comment'), $currentLevel,
        );

        return redirect()->back()->with('success', '却下しました');
    }
}
