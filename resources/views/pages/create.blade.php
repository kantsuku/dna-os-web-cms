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

        {{-- ページタイプ選択 --}}
        <div class="bg-white rounded-lg shadow p-5">
            <label class="block text-sm font-medium text-gray-700 mb-3">ページの種類</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    'top' => ['TOPページ', '🏠'],
                    'lower' => ['下層ページ', '📄'],
                    'blog' => ['ブログ', '📝'],
                    'news' => ['お知らせ', '📢'],
                    'case' => ['症例', '📋'],
                ] as $val => [$label, $icon])
                    <label class="cursor-pointer">
                        <input type="radio" name="page_type" value="{{ $val }}" x-model="pageType" class="sr-only peer">
                        <div class="border-2 rounded-lg p-3 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition">
                            <div class="text-lg">{{ $icon }}</div>
                            <div class="text-sm font-medium">{{ $label }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 基本情報 --}}
        <div class="bg-white rounded-lg shadow p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ページ名 <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="{{ old('title') }}"
                       class="w-full border-gray-300 rounded-lg text-sm px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="例：インプラント治療、医院紹介、スタッフ紹介">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL <span class="text-red-500">*</span></label>
                <div class="flex items-center bg-gray-50 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                    <span class="px-3 text-sm text-gray-400 bg-gray-100 py-3 border-r">{{ $site->domain ?? 'example.com' }}/</span>
                    <input type="text" name="slug" required value="{{ old('slug') }}"
                           class="flex-1 border-0 bg-transparent text-sm px-3 py-3 focus:ring-0"
                           placeholder="implant">
                </div>
                @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">親ページ</label>
                <select name="parent_id" class="w-full border-gray-300 rounded-lg text-sm px-4 py-3">
                    <option value="">なし（トップレベル）</option>
                    @foreach($site->pages()->orderBy('sort_order')->get() as $p)
                        <option value="{{ $p->id }}">{{ str_repeat('　', $p->parent_id ? 1 : 0) }}{{ $p->title }} (/{{ $p->slug }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">子ページとして作成する場合に選択</p>
            </div>

            {{-- DNA-OSキー（下層のみ） --}}
            <div x-show="pageType === 'lower'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">DNA-OS 診療キー</label>
                <input type="text" name="dna_source_key" value="{{ old('dna_source_key') }}"
                       class="w-full border-gray-300 rounded-lg text-sm px-4 py-3"
                       placeholder="02_虫歯治療（空欄でもOK）">
                <p class="text-xs text-gray-400 mt-1">DNA-OSの診療方針と自動連携する場合に入力</p>
            </div>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-sm hover:bg-indigo-700 font-medium">ページを作成</button>
            <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
