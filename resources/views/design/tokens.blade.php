@extends('layouts.app')
@section('title', 'デザイントークン管理')
@section('content')
<h1 class="text-2xl font-bold mb-6">デザイントークン管理</h1>

<form method="POST" action="{{ route('clinic.design.tokens.update', $clinic) }}">
    @csrf
    @method('PUT')

    @forelse($tokens as $category => $categoryTokens)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold capitalize">{{ $category }}</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryTokens as $token)
                <div class="flex items-center space-x-3">
                    <div class="flex-1">
                        <label for="token_{{ $token->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $token->label ?? $token->key }}
                        </label>
                        <div class="flex items-center space-x-2">
                            @if($category === 'color')
                            <input type="color" name="tokens[{{ $token->id }}]" id="token_{{ $token->id }}"
                                value="{{ old('tokens.' . $token->id, $token->value) }}"
                                class="h-10 w-14 border-gray-300 rounded cursor-pointer">
                            <input type="text" value="{{ $token->value }}"
                                class="flex-1 border-gray-300 rounded-md shadow-sm text-sm bg-gray-50"
                                onchange="document.getElementById('token_{{ $token->id }}').value = this.value"
                                oninput="document.getElementById('token_{{ $token->id }}').value = this.value">
                            @else
                            <input type="text" name="tokens[{{ $token->id }}]" id="token_{{ $token->id }}"
                                value="{{ old('tokens.' . $token->id, $token->value) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">--acms-{{ $token->key }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
        デザイントークンがまだ登録されていません
    </div>
    @endforelse

    @if($tokens->isNotEmpty())
    <div class="flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">保存</button>
    </div>
    @endif
</form>
@endsection
