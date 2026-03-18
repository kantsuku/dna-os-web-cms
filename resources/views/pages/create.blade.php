@extends('layouts.app')
@section('title', 'ページ作成 - ' . $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">ページ作成</h1>

<div class="max-w-2xl" x-data="{ pageType: '{{ old('page_type', 'lower') }}' }">
    <form method="POST" action="{{ route('clinic.sites.pages.store', [$clinic, $site]) }}" class="space-y-6">
        @csrf

        {{-- ページの種類 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <label class="block text-sm font-semibold text-gray-900 mb-3">ページの種類</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    'top'   => ['TOPページ', 'サイトのトップ'],
                    'lower' => ['コンテンツページ', '診療・医院紹介等'],
                    'blog'  => ['ブログ', 'スタッフブログ等'],
                    'news'  => ['お知らせ', '医院からの告知'],
                    'case'  => ['症例', '症例紹介（要承認）'],
                ] as $val => [$label, $desc])
                    <label class="cursor-pointer">
                        <input type="radio" name="page_type" value="{{ $val }}" x-model="pageType" class="sr-only peer">
                        <div class="border-2 rounded-lg p-3 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition">
                            <div class="text-sm font-semibold">{{ $label }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $desc }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 基本情報 --}}
        <div class="bg-white rounded-lg shadow p-5 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">ページ名 <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="{{ old('title') }}"
                       class="w-full border border-gray-300 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="例：インプラント治療、医院紹介、スタッフ紹介">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">URL <span class="text-red-500">*</span></label>
                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                    <span class="px-3 py-3 text-sm text-gray-400 bg-gray-50 border-r border-gray-300 whitespace-nowrap">{{ $site->domain ?? 'example.com' }}/</span>
                    <input type="text" name="slug" required value="{{ old('slug') }}"
                           class="flex-1 border-0 text-sm px-3 py-3 focus:ring-0"
                           placeholder="implant">
                </div>
                @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">配置</label>
                <select name="parent_id" class="w-full border border-gray-300 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">トップレベル（ルート直下）</option>
                    @foreach($site->pages()->whereNull('parent_id')->orderBy('sort_order')->get() as $p)
                        <option value="{{ $p->id }}">└ {{ $p->title }} の子ページ</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">既存ページの下に配置する場合に選択</p>
            </div>

            {{-- DNA-OSキー（コンテンツページのみ） --}}
            <div x-show="pageType === 'lower'" x-cloak>
                <label class="block text-sm font-semibold text-gray-900 mb-1">DNA-OS 連携キー <span class="text-xs font-normal text-gray-400">（任意）</span></label>
                <input type="text" name="dna_source_key" value="{{ old('dna_source_key') }}"
                       class="w-full border border-gray-300 rounded-lg text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="例：02_虫歯治療">
                <p class="text-xs text-gray-400 mt-1">DNA-OSの診療方針と自動連携する場合に入力。未入力でも後から設定可能</p>
            </div>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-sm hover:bg-indigo-700 font-medium">ページを作成</button>
            <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
