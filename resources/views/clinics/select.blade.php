@extends('layouts.app')
@section('title', '医院選択')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">医院を選択</h1>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('clinics.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">新規医院登録</a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($clinics as $clinic)
            <a href="{{ route('clinic.dashboard', $clinic) }}"
               class="bg-white rounded-lg border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-md transition group">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600">{{ $clinic->name }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $clinic->clinic_id }}</p>
                    </div>
                    <span class="px-2 py-1 rounded text-xs {{ $clinic->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $clinic->status }}</span>
                </div>
                <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500">
                    <span>サイト: {{ $clinic->sites_count }}</span>
                </div>
            </a>
        @empty
            <div class="col-span-2 bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
                登録された医院がありません。
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('clinics.create') }}" class="text-indigo-600 hover:underline ml-1">新規登録</a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
