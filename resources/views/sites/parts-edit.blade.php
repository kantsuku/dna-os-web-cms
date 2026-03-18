@extends('layouts.app')
@section('title', '共通パーツ - ' . $site->name)

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-2">共通パーツ設定</h1>
<p class="text-sm text-gray-500 mb-6">{{ $site->name }} — ヘッダー / フッター / ナビゲーション</p>

<form method="POST" action="{{ route('clinic.sites.parts.update', [$clinic, $site]) }}"
      x-data="partsEditor(@js($headerConfig), @js($footerConfig))">
    @csrf @method('PUT')

    <div class="space-y-6">
        {{-- ヘッダー --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">ヘッダー</h2>
            </div>

            {{-- ヘッダープレビュー --}}
            <div class="border-b">
                <iframe src="{{ route('clinic.sites.parts.preview-header', [$clinic, $site]) }}"
                        class="w-full border-0" style="height:80px;"></iframe>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">ロゴテキスト</label>
                    <input type="text" name="header[logo_text]" :value="header.logo_text"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="{{ $site->name }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">電話番号</label>
                    <input type="text" name="header[phone]" :value="header.phone"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="000-000-0000">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">CTAボタンテキスト</label>
                    <input type="text" name="header[cta_text]" :value="header.cta_text"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="ご予約・お問い合わせ">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">CTAリンク先</label>
                    <input type="text" name="header[cta_url]" :value="header.cta_url"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="/contact">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">ナビゲーション項目</label>
                    <div class="space-y-2">
                        <template x-for="(item, i) in header.nav_items" :key="i">
                            <div class="flex items-center space-x-2">
                                <input type="text" x-model="item.label" placeholder="ラベル"
                                       class="flex-1 border border-gray-300 rounded text-sm px-3 py-2">
                                <input type="text" x-model="item.url" placeholder="/about"
                                       class="flex-1 border border-gray-300 rounded text-sm px-3 py-2">
                                <button type="button" @click="header.nav_items.splice(i, 1)" class="text-red-500 hover:text-red-700 text-lg px-2">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="header.nav_items.push({label:'', url:''})"
                            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">+ 項目追加</button>
                    <input type="hidden" name="header[nav_items_json]" :value="JSON.stringify(header.nav_items)">
                </div>
            </div>
        </div>

        {{-- フッター --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">フッター</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">医院名</label>
                    <input type="text" name="footer[clinic_name]" value="{{ $footerConfig['clinic_name'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">電話番号</label>
                    <input type="text" name="footer[phone]" value="{{ $footerConfig['phone'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-1">住所</label>
                    <input type="text" name="footer[address]" value="{{ $footerConfig['address'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="東京都○○区...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">診療時間</label>
                    <input type="text" name="footer[hours]" value="{{ $footerConfig['hours'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="9:00〜18:00">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">休診日</label>
                    <input type="text" name="footer[closed_day]" value="{{ $footerConfig['closed_day'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5" placeholder="日曜・祝日">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">コピーライト</label>
                    <input type="text" name="footer[copyright]" value="{{ $footerConfig['copyright'] ?? '' }}"
                           class="w-full border border-gray-300 rounded-lg text-sm px-4 py-2.5">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-indigo-700 font-medium">保存する</button>
        <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg text-sm hover:bg-gray-300">キャンセル</a>
    </div>
</form>

<script>
function partsEditor(header, footer) {
    return { header, footer }
}
</script>
@endsection
