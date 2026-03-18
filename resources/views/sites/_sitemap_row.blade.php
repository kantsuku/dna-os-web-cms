@php
    $gen = $page->currentGeneration;
    $status = $gen?->status ?? $page->status ?? 'draft';
    $sectionsCount = $gen ? count($gen->sections ?? []) : 0;
    $indent = $depth * 28;

    $statusDot = ['draft' => 'bg-gray-300', 'received' => 'bg-yellow-400', 'ready' => 'bg-blue-400', 'approved' => 'bg-indigo-400', 'published' => 'bg-green-400'][$status] ?? 'bg-gray-300';
    $statusLabel = ['draft' => '下書き', 'received' => '取込済', 'ready' => '公開準備', 'approved' => '承認済', 'published' => '公開中'][$status] ?? $status;
@endphp
<div class="group flex items-center px-4 py-2.5 hover:bg-gray-50 transition"
     style="padding-left: {{ 16 + $indent }}px">

    {{-- ツリー線 --}}
    @if($depth > 0)
        <span class="text-gray-300 mr-2 text-xs">{{ $depth === 1 ? '├' : '└' }}</span>
    @endif

    {{-- ステータスドット --}}
    <span class="w-2.5 h-2.5 rounded-full {{ $statusDot }} mr-3 flex-shrink-0" title="{{ $statusLabel }}"></span>

    {{-- ページ名 + URL --}}
    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="flex-1 min-w-0 mr-4">
        <span class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">{{ $page->title }}</span>
        <span class="text-xs text-gray-400 ml-2">/{{ $page->slug }}</span>
    </a>

    {{-- メタ --}}
    <div class="flex items-center space-x-3 flex-shrink-0">
        @if($sectionsCount > 0)
            <span class="text-xs text-gray-400">{{ $sectionsCount }}sec</span>
        @endif

        <span class="text-xs px-1.5 py-0.5 rounded {{ ['draft' => 'bg-gray-100 text-gray-500', 'ready' => 'bg-blue-100 text-blue-600', 'published' => 'bg-green-100 text-green-600'][$status] ?? 'bg-gray-100 text-gray-500' }}">{{ $statusLabel }}</span>

        {{-- 公開操作（ホバー時表示） --}}
        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1">
            @if($status === 'draft' && $gen)
                <form method="POST" action="{{ route('clinic.sites.pages.generations.ready', [$clinic, $site, $page, $gen]) }}" class="inline">
                    @csrf
                    <button class="text-xs bg-blue-500 text-white px-2 py-0.5 rounded hover:bg-blue-600" title="公開準備へ">準備</button>
                </form>
            @endif
            <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="text-xs text-indigo-600 hover:text-indigo-800">編集</a>
        </div>
    </div>
</div>
