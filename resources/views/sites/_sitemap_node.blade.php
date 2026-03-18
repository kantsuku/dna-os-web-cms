@php
    $status = $page->currentGeneration?->status ?? $page->status ?? 'draft';
    $statusColors = [
        'draft' => 'bg-gray-200', 'ready' => 'bg-blue-400', 'approved' => 'bg-indigo-400',
        'published' => 'bg-green-400', 'received' => 'bg-yellow-400',
    ];
    $statusDot = $statusColors[$status] ?? 'bg-gray-200';
    $icons = [
        'home' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'page' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'blog' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
        'news' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>',
        'case' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    ];
    $iconSvg = $icons[$icon] ?? $icons['page'];
    $indent = $depth * 24;
    $sectionsCount = count($page->currentGeneration?->sections ?? []);
@endphp
<a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}"
   class="flex items-center px-3 py-2.5 rounded-lg hover:bg-indigo-50 group transition"
   style="padding-left: {{ 12 + $indent }}px">
    {{-- ツリー線 --}}
    @if($depth > 0)
        <span class="text-gray-300 mr-2">└</span>
    @endif

    {{-- ステータスドット --}}
    <span class="w-2.5 h-2.5 rounded-full {{ $statusDot }} mr-2 flex-shrink-0"></span>

    {{-- アイコン --}}
    <span class="text-gray-400 mr-2 flex-shrink-0">{!! $iconSvg !!}</span>

    {{-- タイトル+URL --}}
    <div class="flex-1 min-w-0">
        <span class="text-sm font-medium text-gray-900 group-hover:text-indigo-700">{{ $page->title }}</span>
        <span class="text-xs text-gray-400 ml-2">/{{ $page->slug }}</span>
    </div>

    {{-- メタ情報 --}}
    <div class="flex items-center space-x-2 flex-shrink-0 text-xs">
        @if($sectionsCount > 0)
            <span class="text-gray-400">{{ $sectionsCount }}sec</span>
        @endif
        <span class="text-gray-400">G{{ $page->currentGeneration?->generation ?? '-' }}</span>
        @php
            $statusLabel = ['draft' => '下書き', 'ready' => '公開準備', 'published' => '公開中', 'received' => '取込済', 'approved' => '承認済'][$status] ?? $status;
            $statusStyle = ['draft' => 'bg-gray-100 text-gray-600', 'ready' => 'bg-blue-100 text-blue-700', 'published' => 'bg-green-100 text-green-700', 'received' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-indigo-100 text-indigo-700'][$status] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <span class="px-1.5 py-0.5 rounded {{ $statusStyle }}">{{ $statusLabel }}</span>
        @if($page->is_published)
            <span class="w-2 h-2 rounded-full bg-green-500" title="公開中"></span>
        @endif
    </div>
</a>
