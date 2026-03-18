@extends('layouts.app')
@section('title', 'コンポーネント一覧')
@section('content')
<h1 class="text-2xl font-bold mb-2">コンポーネント一覧</h1>
<p class="text-sm text-gray-500 mb-6">clinic-page-generatorが出力するcom-コンポーネント体系</p>

@forelse($components as $category => $categoryComponents)
<div class="mb-8">
    <h2 class="text-lg font-semibold mb-3 capitalize flex items-center">
        @php
            $catColors = ['layout' => 'blue', 'heading' => 'purple', 'content' => 'green', 'cta' => 'red', 'utility' => 'gray'];
            $cc = $catColors[$category] ?? 'gray';
        @endphp
        <span class="w-3 h-3 rounded-full bg-{{ $cc }}-400 mr-2"></span>
        {{ $category }}
        <span class="text-sm font-normal text-gray-400 ml-2">({{ $categoryComponents->count() }})</span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($categoryComponents as $component)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-indigo-300 transition" x-data="{ showCode: false }">
            {{-- ヘッダー --}}
            <div class="px-4 py-3 border-b bg-gray-50">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $component->name }}</h3>
                        <code class="text-xs text-indigo-600">.{{ $component->key }}</code>
                    </div>
                    @if($component->variants)
                        <div class="flex flex-wrap gap-1">
                            @foreach($component->variants as $v)
                                <span class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded">{{ $v }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- プレビュー --}}
            <div class="p-4 border-b min-h-[80px]">
                @if($component->preview_html)
                    <div class="rounded bg-gray-50 p-3 overflow-hidden max-h-48 text-sm">
                        {!! $component->preview_html !!}
                    </div>
                @elseif($component->html_template)
                    <div class="rounded bg-gray-50 p-3 overflow-hidden max-h-48 text-sm">
                        {!! $component->html_template !!}
                    </div>
                @else
                    {{-- CSSクラス名からサンプルプレビューを生成 --}}
                    <div class="rounded bg-gray-50 p-3 text-sm">
                        <div class="{{ $component->key }}" style="min-height: 40px; border: 1px dashed #ddd; padding: 8px; border-radius: 4px;">
                            <span class="text-gray-400 text-xs">.{{ $component->key }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 説明 + HTMLテンプレート --}}
            <div class="px-4 py-3">
                @if($component->description)
                    <p class="text-xs text-gray-500 mb-2">{{ $component->description }}</p>
                @endif

                @if($component->html_template)
                    <button @click="showCode = !showCode" class="text-xs text-indigo-600 hover:text-indigo-800">
                        <span x-text="showCode ? 'HTMLを閉じる' : 'HTMLを見る'"></span>
                    </button>
                    <div x-show="showCode" style="display:none" class="mt-2">
                        <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded overflow-x-auto max-h-48"><code>{{ $component->html_template }}</code></pre>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
    コンポーネントがまだ登録されていません
</div>
@endforelse
@endsection
