@extends('layouts.app')
@section('title', $page->title . ' - 設定')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $page->title }}に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">ページ設定</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">
    <form method="POST" action="{{ route('clinic.sites.pages.update', [$clinic, $site, $page]) }}" class="bg-white rounded-lg shadow p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ページ名</label>
            <input type="text" name="title" required value="{{ old('title', $page->title) }}" class="w-full border-gray-300 rounded text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">URL（スラッグ）</label>
            <div class="flex items-center">
                <span class="text-sm text-gray-400 mr-1">/</span>
                <input type="text" name="slug" required value="{{ old('slug', $page->slug) }}" class="flex-1 border-gray-300 rounded text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ページタイプ</label>
            <select name="page_type" class="w-full border-gray-300 rounded text-sm">
                @foreach(['top' => 'TOPページ', 'lower' => '診療・下層ページ', 'blog' => 'ブログ', 'news' => 'お知らせ', 'case' => '症例'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('page_type', $page->page_type) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">親ページ</label>
            <select name="parent_id" class="w-full border-gray-300 rounded text-sm">
                <option value="">なし（トップレベル）</option>
                @foreach($site->pages()->where('id', '!=', $page->id)->orderBy('sort_order')->get() as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id', $page->parent_id) == $p->id)>{{ $p->title }} (/{{ $p->slug }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">子ページにする場合、親ページを選択</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">メタディスクリプション</label>
            <textarea name="meta[description]" rows="2" class="w-full border-gray-300 rounded text-sm"
                      placeholder="検索結果に表示される説明文（120文字程度）">{{ $page->meta['description'] ?? '' }}</textarea>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">保存</button>
            <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>

    {{-- 情報カード --}}
    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">ページ情報</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-gray-400">ID</dt><dd class="font-mono">{{ $page->id }}</dd></div>
                <div><dt class="text-gray-400">コンテンツ分類</dt><dd>{{ $page->content_classification }}</dd></div>
                <div><dt class="text-gray-400">テンプレート</dt><dd>{{ $page->template_key }}</dd></div>
                <div><dt class="text-gray-400">世代数</dt><dd>{{ $page->generations()->count() }}</dd></div>
                <div><dt class="text-gray-400">作成日</dt><dd>{{ $page->created_at->format('Y/m/d H:i') }}</dd></div>
                @if($page->dna_source_key)
                    <div><dt class="text-gray-400">DNA-OSキー</dt><dd class="font-mono">{{ $page->dna_source_key }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="bg-red-50 rounded-lg shadow p-5">
            <h3 class="text-sm font-semibold text-red-700 mb-2">危険な操作</h3>
            <p class="text-xs text-red-600 mb-3">この操作は取り消せません</p>
            <form method="POST" action="{{ route('clinic.sites.pages.destroy', [$clinic, $site, $page]) }}"
                  onsubmit="return confirm('本当にこのページを削除しますか？')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">ページを削除</button>
            </form>
        </div>
    </div>
</div>
@endsection
