@extends('layouts.app')
@section('title', 'デザイン設定 - ' . $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-6">デザイン設定 — {{ $site->name }}</h1>

<form method="POST" action="{{ route('clinic.design.site.update', [$clinic, $site]) }}">
    @csrf
    @method('PUT')

    {{-- トークン上書き --}}
    @foreach($globalTokens as $category => $tokens)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-3 border-b bg-gray-50">
            <h2 class="text-sm font-semibold capitalize">{{ $category }}</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($tokens as $token)
                @php
                    $overrideValue = $design?->tokens[$token->key] ?? '';
                @endphp
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $token->label ?? $token->key }}</label>
                    @if($token->category === 'color')
                        <div class="flex items-center space-x-2">
                            <input type="color" name="tokens[{{ $token->key }}]"
                                value="{{ $overrideValue ?: $token->value }}"
                                class="h-9 w-12 border-gray-300 rounded cursor-pointer">
                            <span class="text-xs text-gray-400">{{ $token->value }}</span>
                        </div>
                    @else
                        <input type="text" name="tokens[{{ $token->key }}]"
                            value="{{ $overrideValue }}"
                            placeholder="{{ $token->value }}"
                            class="w-full border-gray-300 rounded text-sm">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    {{-- カスタムCSS --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-3 border-b bg-gray-50">
            <h2 class="text-sm font-semibold">カスタムCSS</h2>
        </div>
        <div class="p-6">
            <textarea name="custom_css" rows="12"
                class="w-full font-mono text-sm border-gray-300 rounded bg-gray-900 text-green-400 p-3"
                placeholder="/* サイト固有のCSS */">{{ $design?->custom_css }}</textarea>
        </div>
    </div>

    <div class="flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">保存</button>
        <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-300">キャンセル</a>
    </div>
</form>
@endsection
