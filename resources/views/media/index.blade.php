@extends('layouts.app')
@section('title', 'メディア管理')

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">メディアライブラリ</h1>
        {{-- パンくず --}}
        <div class="flex items-center space-x-1 text-sm mt-1">
            <a href="{{ route('clinic.media.index', $clinic) }}" class="text-indigo-600 hover:text-indigo-800">ルート</a>
            @foreach($breadcrumb as $crumb)
                <span class="text-gray-400">/</span>
                <a href="{{ route('clinic.media.index', [$clinic, 'folder' => $crumb->id]) }}" class="text-indigo-600 hover:text-indigo-800">{{ $crumb->name }}</a>
            @endforeach
        </div>
    </div>
    <div class="flex space-x-2">
        {{-- フォルダ作成 --}}
        <div x-data="{ open: false }">
            <button @click="open = !open" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">+ フォルダ</button>
            <div x-show="open" style="display:none" class="absolute mt-1 bg-white rounded-lg shadow-lg border p-3 z-10">
                <form method="POST" action="{{ route('clinic.media.folder', $clinic) }}" class="flex space-x-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                    <input type="text" name="name" required class="border border-gray-300 rounded text-sm px-3 py-1.5" placeholder="フォルダ名">
                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm">作成</button>
                </form>
            </div>
        </div>

        {{-- アップロード --}}
        <form method="POST" action="{{ route('clinic.media.upload', $clinic) }}" enctype="multipart/form-data"
              x-data="{ files: null }" class="flex space-x-2">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
            <label class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 cursor-pointer">
                アップロード
                <input type="file" name="files[]" multiple accept="image/*" class="sr-only" @change="files = $event.target.files; $el.closest('form').submit()">
            </label>
        </form>
    </div>
</div>

{{-- フォルダ --}}
@if($folders->isNotEmpty())
    <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 mb-6">
        @foreach($folders as $folder)
            <a href="{{ route('clinic.media.index', [$clinic, 'folder' => $folder->id]) }}"
               class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:border-indigo-300 hover:shadow transition">
                <div class="text-3xl mb-1">📁</div>
                <div class="text-xs text-gray-700 truncate">{{ $folder->name }}</div>
            </a>
        @endforeach
    </div>
@endif

{{-- ファイル --}}
@if($files->isNotEmpty())
    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($files as $file)
            <div class="group relative bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-indigo-300 hover:shadow transition">
                @if($file->isImage())
                    <div class="aspect-square bg-gray-100">
                        <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" class="w-full h-full object-cover rounded-none">
                    </div>
                @else
                    <div class="aspect-square bg-gray-50 flex items-center justify-center text-3xl">📄</div>
                @endif
                <div class="p-2">
                    <p class="text-xs text-gray-700 truncate">{{ $file->original_name }}</p>
                    <p class="text-xs text-gray-400">{{ $file->human_size }}{{ $file->width ? " ({$file->width}x{$file->height})" : '' }}</p>
                </div>
                {{-- ホバーアクション --}}
                <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <form method="POST" action="{{ route('clinic.media.destroy', [$clinic, $file]) }}"
                          onsubmit="return confirm('削除しますか？')">
                        @csrf @method('DELETE')
                        <button class="bg-red-500 text-white w-6 h-6 rounded-full text-xs hover:bg-red-600">&times;</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $files->links() }}</div>
@elseif($folders->isEmpty())
    <div class="bg-white rounded-lg border p-12 text-center text-gray-500">
        <p class="text-lg mb-2">ファイルがありません</p>
        <p class="text-sm">上の「アップロード」ボタンから画像をアップロードしてください</p>
    </div>
@endif
@endsection
