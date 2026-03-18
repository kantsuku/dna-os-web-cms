@extends('layouts.app')
@section('title', '症例作成 - ' . $site->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.exceptions.index', [$clinic, $site]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; 例外コンテンツ一覧</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-6">例外コンテンツ作成</h1>

<form method="POST" action="{{ route('clinic.sites.exceptions.store', [$clinic, $site]) }}" class="max-w-3xl">
    @csrf

    <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">対象ページ</label>
            <select name="page_id" required class="w-full border-gray-300 rounded text-sm">
                @foreach($pages as $p)
                    <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->slug }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">コンテンツタイプ</label>
            <select name="content_type" required class="w-full border-gray-300 rounded text-sm">
                <option value="case">症例</option>
                <option value="medical_ad_gl">医療広告GL配慮</option>
                <option value="effect_claim">効果訴求</option>
                <option value="other">その他</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
            <input type="text" name="title" required class="w-full border-gray-300 rounded text-sm" placeholder="例: インプラント治療（60代男性）">
        </div>

        {{-- 症例構造化データ --}}
        <div class="bg-gray-50 rounded p-4 space-y-4" x-data="{ showStructured: true }">
            <h3 class="text-sm font-medium text-gray-700">症例構造化データ（医療広告GL必須項目）</h3>

            <div>
                <label class="block text-xs text-gray-500 mb-1">主訴 <span class="text-red-500">*</span></label>
                <input type="text" name="structured_data[chief_complaint]" class="w-full border-gray-300 rounded text-sm" placeholder="例: 右下奥歯の欠損">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">治療内容 <span class="text-red-500">*</span></label>
                <input type="text" name="structured_data[treatment]" class="w-full border-gray-300 rounded text-sm" placeholder="例: インプラント埋入（1本）">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">治療期間 <span class="text-red-500">*</span></label>
                    <input type="text" name="structured_data[duration]" class="w-full border-gray-300 rounded text-sm" placeholder="例: 約6ヶ月">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">費用 <span class="text-red-500">*</span></label>
                    <input type="text" name="structured_data[cost]" class="w-full border-gray-300 rounded text-sm" placeholder="例: 385,000円（税込）">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">リスク・副作用 <span class="text-red-500">*</span></label>
                <textarea name="structured_data[risks]" rows="2" class="w-full border-gray-300 rounded text-sm" placeholder="例: 感染、神経損傷、インプラント周囲炎のリスク"></textarea>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">年齢・性別</label>
                <input type="text" name="structured_data[age_gender]" class="w-full border-gray-300 rounded text-sm" placeholder="例: 60代男性">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">本文HTML</label>
            <textarea name="content_html" rows="10" required class="w-full font-mono text-sm border-gray-300 rounded"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">コンプライアンスメモ</label>
            <textarea name="compliance_notes" rows="3" class="w-full border-gray-300 rounded text-sm" placeholder="確認事項や法務への申し送り等"></textarea>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">作成してチェック実行</button>
            <a href="{{ route('clinic.sites.exceptions.index', [$clinic, $site]) }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-200">キャンセル</a>
        </div>
    </div>
</form>
@endsection
