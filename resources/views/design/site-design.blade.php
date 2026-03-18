@extends('layouts.app')
@section('title', 'デザイン設定 - ' . $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-6">デザイン設定 - {{ $site->name }}</h1>

<form method="POST" action="{{ route('clinic.design.site.update', [$clinic, $site]) }}">
    @csrf
    @method('PUT')

    {{-- グローバルトークンの上書き --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold">トークン上書き</h2>
            <p class="text-xs text-gray-500 mt-1">グローバルのデフォルト値を上書きするトークンを設定してください</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($globalTokens as $token)
                @php
                    $overrideValue = $design?->tokens[$token->key] ?? '';
                @endphp
                <div>
                    <label for="token_{{ $token->key }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $token->label ?? $token->key }}
                    </label>
                    <div class="flex items-center space-x-2">
                        @if($token->category === 'color')
                        <input type="color" name="tokens[{{ $token->key }}]" id="token_{{ $token->key }}"
                            value="{{ old('tokens.' . $token->key, $overrideValue ?: $token->value) }}"
                            class="h-10 w-14 border-gray-300 rounded cursor-pointer">
                        @else
                        <input type="text" name="tokens[{{ $token->key }}]" id="token_{{ $token->key }}"
                            value="{{ old('tokens.' . $token->key, $overrideValue) }}"
                            placeholder="{{ $token->value }}"
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1">デフォルト: {{ $token->value }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- カスタムCSS --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold">カスタムCSS</h2>
            <p class="text-xs text-gray-500 mt-1">このサイト固有のCSSスタイルを記述してください</p>
        </div>
        <div class="p-6">
            <textarea name="custom_css" id="custom_css" rows="15"
                class="w-full font-mono text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('custom_css') border-red-500 @enderror"
                placeholder="/* サイト固有のCSS */">{{ old('custom_css', $design?->custom_css) }}</textarea>
            @error('custom_css')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">保存</button>
        <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
    </div>
</form>
@endsection
