@extends('layouts.app')
@section('title', $page->title . ' - セクション編集')

@section('content')
<div class="mb-4">
    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ $page->title }}に戻る</a>
</div>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">セクション編集</h1>
        <p class="text-sm text-gray-500">{{ $section['heading'] ?: $sectionId }}
            @if(($section['lock_status'] ?? 'unlocked') !== 'unlocked')
                <span class="ml-1 px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">🔒 {{ $section['lock_status'] }}</span>
            @endif
        </p>
    </div>
</div>

<div x-data="sectionEditor()" class="space-y-4">
    {{-- タブ --}}
    <div class="flex space-x-1 bg-gray-100 rounded-lg p-1 w-fit">
        <button type="button" @click="mode = 'visual'"
                :class="mode === 'visual' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-1.5 rounded-md text-sm font-medium transition">ビジュアル編集</button>
        <button type="button" @click="switchToHtml()"
                :class="mode === 'html' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-1.5 rounded-md text-sm font-medium transition">HTML編集</button>
    </div>

    {{-- ビジュアルモード: iframe内でcontenteditable --}}
    <div x-show="mode === 'visual'" class="bg-white rounded-lg shadow overflow-hidden">
        <iframe id="visual-frame"
                src="{{ route('clinic.sites.pages.section-frame', [$clinic, $site, $page, $sectionId, 'editable' => 1]) }}"
                class="w-full border-0"
                style="min-height: 400px;"
                onload="this.style.height = Math.max(400, this.contentDocument.documentElement.scrollHeight) + 'px'"
        ></iframe>
    </div>

    {{-- HTMLモード --}}
    <div x-show="mode === 'html'" style="display:none">
        <textarea x-ref="htmlEditor" rows="20"
                  class="w-full font-mono text-sm border border-gray-300 rounded-lg p-4 bg-gray-900 text-green-400 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
    </div>

    {{-- 保存フォーム --}}
    <form method="POST" action="{{ route('clinic.sites.pages.sections.update', [$clinic, $site, $page, $sectionId]) }}"
          @submit="beforeSubmit($event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="content_html" x-ref="submitHtml">

        <div class="bg-white rounded-lg shadow p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">変更理由 <span class="text-red-500">*</span></label>
                <input type="text" name="patch_reason" required
                       class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="例：診療方針の変更に伴い説明文を修正">
            </div>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="lock_after_edit" value="1" class="rounded border-gray-300 text-indigo-600" checked>
                <span class="text-sm text-gray-700">編集後にロックする（AI再生成時にスキップ）</span>
            </label>
            <div class="flex space-x-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-indigo-700 font-medium">保存</button>
                <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg text-sm hover:bg-gray-300">キャンセル</a>
            </div>
        </div>
    </form>
</div>

<script>
function sectionEditor() {
    return {
        mode: 'visual',
        switchToHtml() {
            // iframe内のHTMLを取得してテキストエリアに
            const frame = document.getElementById('visual-frame');
            const html = frame.contentDocument.body.innerHTML;
            this.$refs.htmlEditor.value = html;
            this.mode = 'html';
        },
        beforeSubmit(e) {
            let html;
            if (this.mode === 'visual') {
                const frame = document.getElementById('visual-frame');
                html = frame.contentDocument.body.innerHTML;
            } else {
                html = this.$refs.htmlEditor.value;
            }
            this.$refs.submitHtml.value = html;
        }
    }
}
</script>
@endsection
