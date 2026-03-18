@extends('layouts.app')
@section('title', $site->name . ' - ページ作成')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">ページ作成</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl" x-data="pageCreate()">

    {{-- ステップ1: 何を作る？ --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">どんなページを作りますか？</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @php
                $pageTypes = [
                    ['value' => 'top', 'label' => 'TOPページ', 'desc' => 'サイトのトップページ', 'icon' => '🏠'],
                    ['value' => 'lower', 'label' => '診療ページ', 'desc' => '虫歯治療、インプラント等', 'icon' => '🦷'],
                    ['value' => 'lower', 'label' => '医院紹介', 'desc' => '理念、スタッフ、設備等', 'icon' => '🏥', 'template' => 'about'],
                    ['value' => 'blog', 'label' => 'ブログ', 'desc' => 'スタッフブログ、コラム', 'icon' => '📝'],
                    ['value' => 'news', 'label' => 'お知らせ', 'desc' => '医院からのお知らせ', 'icon' => '📢'],
                    ['value' => 'case', 'label' => '症例', 'desc' => '症例紹介（要承認）', 'icon' => '📋'],
                ];
            @endphp
            @foreach($pageTypes as $pt)
                <button type="button"
                        @click="selectType('{{ $pt['value'] }}', '{{ $pt['template'] ?? $pt['value'] }}')"
                        :class="pageType === '{{ $pt['value'] }}' && templateKey === '{{ $pt['template'] ?? $pt['value'] }}' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-300'"
                        class="border rounded-lg p-3 text-left transition">
                    <div class="text-xl mb-1">{{ $pt['icon'] }}</div>
                    <div class="text-sm font-medium text-gray-900">{{ $pt['label'] }}</div>
                    <div class="text-xs text-gray-500">{{ $pt['desc'] }}</div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ステップ2: 詳細 --}}
    <form method="POST" action="{{ route('clinic.sites.pages.store', [$clinic, $site]) }}">
        @csrf
        <input type="hidden" name="page_type" x-model="pageType">
        <input type="hidden" name="template_key" x-model="templateKey">

        <div class="space-y-4 border-t pt-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ページ名 <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="{{ old('title') }}"
                    class="w-full border-gray-300 rounded text-sm" placeholder="例: インプラント、医院紹介、虫歯治療">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL（スラッグ） <span class="text-red-500">*</span></label>
                <div class="flex items-center">
                    <span class="text-sm text-gray-400 mr-1">{{ $site->domain }}/</span>
                    <input type="text" name="slug" required value="{{ old('slug') }}"
                        class="flex-1 border-gray-300 rounded text-sm" placeholder="implant">
                </div>
                @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- 診療ページの場合のみ表示 --}}
            <div x-show="pageType === 'lower'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">DNA-OSの診療キー</label>
                <input type="text" name="dna_source_key" value="{{ old('dna_source_key') }}"
                    class="w-full border-gray-300 rounded text-sm" placeholder="例: 02_虫歯治療、implant（clinic-page-generatorのinternal_key）">
                <p class="text-xs text-gray-400 mt-1">DNA-OSの診療方針と紐づける場合に入力。空欄でもOK</p>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">作成する</button>
            <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>

<script>
function pageCreate() {
    return {
        pageType: '{{ old('page_type', 'lower') }}',
        templateKey: '{{ old('template_key', 'lower') }}',
        selectType(type, template) {
            this.pageType = type;
            this.templateKey = template;
        }
    }
}
</script>
@endsection
