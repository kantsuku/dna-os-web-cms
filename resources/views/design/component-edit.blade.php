@extends('layouts.app')
@section('title', $component->name . ' - 編集')

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.design.components', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; コンポーネント一覧</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-2">コンポーネント編集</h1>
<p class="text-sm text-gray-500 mb-6"><code>.{{ $component->key }}</code> — {{ $component->category }}</p>

<form method="POST" action="{{ route('clinic.design.components.update', [$clinic, $component]) }}"
      x-data="compEditor(@js($component->preview_html ?? ''))">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 左: フォーム --}}
        <div class="space-y-5">
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">コンポーネント名</label>
                    <input type="text" name="name" required value="{{ $component->name }}" class="w-full border-gray-300 rounded text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                    <textarea name="description" rows="2" class="w-full border-gray-300 rounded text-sm">{{ $component->description }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">バリアント（カンマ区切り）</label>
                    <input type="text" name="variants" value="{{ $component->variants ? implode(', ', $component->variants) : '' }}"
                           class="w-full border-gray-300 rounded text-sm" placeholder="例: _white, _lg, _right">
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">HTMLテンプレート</label>
                <textarea name="html_template" rows="10"
                          class="w-full font-mono text-xs border-gray-300 rounded bg-gray-900 text-green-400 p-3">{{ $component->html_template }}</textarea>
                <p class="text-xs text-gray-400 mt-1">このコンポーネントの構造を定義するHTMLテンプレート</p>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">プレビューHTML</label>
                <textarea name="preview_html" rows="8" x-model="previewHtml"
                          class="w-full font-mono text-xs border-gray-300 rounded bg-gray-900 text-green-400 p-3">{{ $component->preview_html }}</textarea>
                <p class="text-xs text-gray-400 mt-1">コンポーネント一覧に表示されるプレビュー用HTML</p>
            </div>
        </div>

        {{-- 右: ライブプレビュー --}}
        <div>
            <div class="bg-white rounded-lg border border-gray-200 sticky top-4">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold">ライブプレビュー</h3>
                </div>
                <div class="p-6">
                    <div x-html="previewHtml" class="min-h-[200px]"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">保存する</button>
        <a href="{{ route('clinic.design.components', $clinic) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-300">キャンセル</a>
    </div>
</form>

<script>
function compEditor(initialPreview) {
    return { previewHtml: initialPreview }
}
</script>
@endsection
