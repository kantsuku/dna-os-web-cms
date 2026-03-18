@extends('layouts.app')
@section('title', 'サイト一覧')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">サイト一覧</h1>
    <a href="{{ route('sites.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">新規サイト作成</a>
</div>

<div class="bg-white rounded-lg shadow">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">サイト名</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ドメイン</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ページ数</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($sites as $site)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500">{{ $site->id }}</td>
                <td class="px-6 py-4 font-medium">{{ $site->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $site->domain ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">{{ $site->pages_count ?? $site->pages->count() }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $site->status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('sites.show', $site) }}" class="text-indigo-600 hover:underline text-sm">詳細</a>
                    <a href="{{ route('sites.edit', $site) }}" class="text-gray-600 hover:underline text-sm">編集</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">サイトがまだ登録されていません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
