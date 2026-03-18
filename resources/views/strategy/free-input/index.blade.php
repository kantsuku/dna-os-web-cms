@extends('layouts.app')
@section('title', 'フリー入力修正依頼')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">フリー入力修正依頼</h1>

{{-- 新規依頼フォーム --}}
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">新規修正依頼</h2>
    <form method="POST" action="{{ route('strategy.free-input.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">対象サイト</label>
            <select name="site_id" required class="w-full border-gray-300 rounded text-sm">
                <option value="">選択してください</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">修正依頼内容</label>
            <textarea name="raw_text" required rows="4" class="w-full border-gray-300 rounded text-sm" placeholder="例：トップページの院長あいさつを変更したい"></textarea>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">送信する</button>
    </form>
</div>

{{-- 依頼履歴 --}}
<h2 class="text-lg font-medium text-gray-900 mb-3">依頼履歴</h2>
<div class="space-y-3">
    @forelse($requests as $req)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-sm text-gray-900">{{ Str::limit($req->raw_text, 100) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $req->submitter->name }} | {{ $req->site?->name }} | {{ $req->created_at->format('Y/m/d H:i') }}</p>
                </div>
                @php
                    $isColors = ['pending' => 'gray', 'interpreted' => 'blue', 'confirmed' => 'green', 'rejected' => 'red'];
                    $ic = $isColors[$req->interpretation_status] ?? 'gray';
                @endphp
                <span class="px-2 py-1 rounded text-xs bg-{{ $ic }}-100 text-{{ $ic }}-700">{{ $req->interpretation_status }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">依頼はありません</p>
    @endforelse
</div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
