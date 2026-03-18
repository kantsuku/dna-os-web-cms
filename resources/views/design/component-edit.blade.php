@extends('layouts.app')
@section('title', $component->name . ' - 編集')

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.design.components', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; コンポーネント一覧</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $component->name }}</h1>
<p class="text-sm text-gray-500 mb-6"><code class="bg-gray-100 px-2 py-0.5 rounded">.{{ $component->key }}</code> — {{ $component->category }}</p>

<form method="POST" action="{{ route('clinic.design.components.update', [$clinic, $component]) }}"
      x-data="compEditor(@js($component->preview_html ?? ''), @js($component->custom_css ?? ''))">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- 左: エディタ --}}
        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow p-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">コンポーネント名</label>
                    <input type="text" name="name" required value="{{ $component->name }}" class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">説明</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5">{{ $component->description }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">バリアント（カンマ区切り）</label>
                    <input type="text" name="variants" value="{{ $component->variants ? implode(', ', $component->variants) : '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="_white, _lg, _right">
                </div>
            </div>

            {{-- HTML --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <label class="text-sm font-semibold text-gray-900">HTML</label>
                </div>
                <textarea name="preview_html" rows="12" x-model="previewHtml"
                          class="w-full font-mono text-xs border-0 p-4 bg-gray-950 text-green-400 focus:ring-0 rounded-b-lg">{{ $component->preview_html }}</textarea>
            </div>

            {{-- CSS --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <label class="text-sm font-semibold text-gray-900">カスタムCSS</label>
                    <span class="text-xs text-gray-400 ml-2">（このコンポーネント固有のスタイル）</span>
                </div>
                <textarea name="custom_css" rows="8" x-model="customCss"
                          class="w-full font-mono text-xs border-0 p-4 bg-gray-950 text-blue-400 focus:ring-0 rounded-b-lg"
                          placeholder=".{{ $component->key }} { }">{{ $component->custom_css }}</textarea>
            </div>

            {{-- HTMLテンプレート（構造定義） --}}
            <details class="bg-white rounded-lg shadow">
                <summary class="px-5 py-3 cursor-pointer text-sm font-semibold text-gray-700 hover:bg-gray-50">HTMLテンプレート（上級者向け）</summary>
                <textarea name="html_template" rows="8"
                          class="w-full font-mono text-xs border-0 border-t p-4 bg-gray-950 text-yellow-400 focus:ring-0 rounded-b-lg">{{ $component->html_template }}</textarea>
            </details>
        </div>

        {{-- 右: ライブプレビュー --}}
        <div>
            <div class="bg-white rounded-lg shadow sticky top-4">
                <div class="px-5 py-3 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold">ライブプレビュー</h3>
                    <span class="text-xs text-gray-400">com-CSS + カスタムCSS適用</span>
                </div>
                <div class="p-0">
                    <iframe :srcdoc="buildPreviewDoc()" class="w-full border-0 rounded-b-lg" style="min-height: 300px;"
                            @load="$el.style.height = Math.max(300, $el.contentDocument.documentElement.scrollHeight) + 'px'"></iframe>
                </div>
            </div>

            <div class="mt-4 flex space-x-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-indigo-700 font-medium">保存</button>
                <a href="{{ route('clinic.design.components', $clinic) }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg text-sm hover:bg-gray-300">キャンセル</a>
            </div>
        </div>
    </div>
</form>

<script>
function compEditor(initialHtml, initialCss) {
    return {
        previewHtml: initialHtml,
        customCss: initialCss,
        buildPreviewDoc() {
            return `<!DOCTYPE html><html><head><meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>${this.getBaseCss()}${this.customCss}</style>
</head><body style="margin:0;padding:16px">${this.previewHtml}</body></html>`;
        },
        getBaseCss() {
            return @js(file_get_contents(resource_path('site-assets/default/css/theme-base.css')));
        }
    }
}
</script>
@endsection
