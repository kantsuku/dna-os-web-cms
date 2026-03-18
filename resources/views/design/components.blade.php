@extends('layouts.app')
@section('title', 'コンポーネント一覧')
@section('content')
<h1 class="text-2xl font-bold mb-6">コンポーネント一覧</h1>

@forelse($components as $category => $categoryComponents)
<div class="mb-8">
    <h2 class="text-lg font-semibold mb-4 capitalize">{{ $category }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categoryComponents as $component)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-semibold">{{ $component->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $component->key }}</p>
                </div>
            </div>
            {{-- プレビュー領域 --}}
            <div class="p-4 border-b">
                @if($component->preview_html)
                <div class="border rounded p-3 bg-gray-50 overflow-hidden max-h-48">
                    {!! $component->preview_html !!}
                </div>
                @else
                <div class="text-center text-sm text-gray-400 py-6">プレビューなし</div>
                @endif
            </div>
            {{-- 説明 --}}
            @if($component->description)
            <div class="px-4 py-3">
                <p class="text-xs text-gray-500">{{ $component->description }}</p>
            </div>
            @endif
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
