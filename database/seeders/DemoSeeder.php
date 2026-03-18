<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\DeployRecord;
use App\Models\DesignToken;
use App\Models\Page;
use App\Models\PageGeneration;
use App\Models\Site;
use App\Models\SiteDesign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── ユーザー ──
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@acms.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => '編集者',
            'email' => 'editor@acms.local',
            'password' => Hash::make('password'),
            'role' => 'editor',
        ]);

        // ── デザイントークン（グローバルデフォルト） ──
        $this->seedDesignTokens();

        // ── コンポーネント定義 ──
        $this->seedComponents();

        // ── デモサイト ──
        $site = Site::create([
            'clinic_id' => 'demo_clinic_001',
            'name' => 'デモ矯正歯科',
            'domain' => 'demo-ortho.example.com',
            'status' => 'active',
        ]);

        $design = SiteDesign::create([
            'site_id' => $site->id,
            'name' => 'default',
            'tokens' => ['color-primary' => '#2563eb'],
            'status' => 'active',
        ]);

        $site->update(['design_id' => $design->id]);
        $site->users()->attach($admin->id);

        // ── デモページ + 世代 ──
        $this->seedDemoPages($site, $admin);
    }

    private function seedDesignTokens(): void
    {
        $tokens = [
            ['category' => 'color', 'key' => 'color-primary', 'value' => '#2563eb', 'label' => 'プライマリカラー', 'sort_order' => 1],
            ['category' => 'color', 'key' => 'color-primary-dark', 'value' => '#1d4ed8', 'label' => 'プライマリ（暗）', 'sort_order' => 2],
            ['category' => 'color', 'key' => 'color-primary-light', 'value' => '#eff6ff', 'label' => 'プライマリ（淡）', 'sort_order' => 3],
            ['category' => 'color', 'key' => 'color-text', 'value' => '#1f2937', 'label' => 'テキスト色', 'sort_order' => 4],
            ['category' => 'color', 'key' => 'color-text-light', 'value' => '#6b7280', 'label' => 'テキスト色（薄）', 'sort_order' => 5],
            ['category' => 'color', 'key' => 'color-bg', 'value' => '#ffffff', 'label' => '背景色', 'sort_order' => 6],
            ['category' => 'color', 'key' => 'color-bg-alt', 'value' => '#f9fafb', 'label' => '背景色（交互）', 'sort_order' => 7],
            ['category' => 'color', 'key' => 'color-border', 'value' => '#e5e7eb', 'label' => 'ボーダー色', 'sort_order' => 8],
            ['category' => 'font', 'key' => 'font-base', 'value' => "'Noto Sans JP', sans-serif", 'label' => '本文フォント', 'sort_order' => 1],
            ['category' => 'font', 'key' => 'font-heading', 'value' => "'Noto Serif JP', serif", 'label' => '見出しフォント', 'sort_order' => 2],
            ['category' => 'spacing', 'key' => 'spacing-section', 'value' => '60px', 'label' => 'セクション間隔', 'sort_order' => 1],
            ['category' => 'spacing', 'key' => 'max-width', 'value' => '1100px', 'label' => '最大幅', 'sort_order' => 2],
            ['category' => 'radius', 'key' => 'radius-base', 'value' => '8px', 'label' => '角丸', 'sort_order' => 1],
        ];

        foreach ($tokens as $t) {
            DesignToken::create($t);
        }
    }

    private function seedComponents(): void
    {
        $components = [
            ['key' => 'acms-section', 'name' => 'セクション', 'category' => 'layout', 'description' => '白背景のセクション', 'preview_html' => '<div class="acms-section"><div class="container"><p>セクションコンテンツ</p></div></div>'],
            ['key' => 'acms-section--alt', 'name' => 'セクション（グレー）', 'category' => 'layout', 'description' => 'グレー背景の交互セクション', 'preview_html' => '<div class="acms-section--alt"><div class="container"><p>交互セクションコンテンツ</p></div></div>'],
            ['key' => 'acms-page-header', 'name' => 'ページヘッダー', 'category' => 'heading', 'description' => 'ページ冒頭の大見出し+リード文', 'preview_html' => '<div class="acms-page-header"><h1>ページタイトル</h1><p class="lead">リード文がここに入ります</p></div>'],
            ['key' => 'acms-h2', 'name' => '見出し H2', 'category' => 'heading', 'description' => 'セクション見出し', 'preview_html' => '<h2 class="acms-h2">見出しテキスト</h2>'],
            ['key' => 'acms-h3', 'name' => '見出し H3', 'category' => 'heading', 'description' => 'サブ見出し', 'preview_html' => '<h3 class="acms-h3">サブ見出しテキスト</h3>'],
            ['key' => 'acms-media', 'name' => 'メディア（画像+テキスト）', 'category' => 'layout', 'description' => '2カラム: 画像とテキスト', 'preview_html' => '<div class="acms-media acms-media--right"><div><p>テキストが入ります。画像と並んで表示されます。</p></div><img src="https://placehold.co/400x300" alt="sample"></div>'],
            ['key' => 'acms-grid', 'name' => 'グリッド', 'category' => 'layout', 'description' => '2〜3カラムのグリッドレイアウト', 'preview_html' => '<div class="acms-grid acms-grid--col-3"><div style="background:#f3f4f6;padding:1rem">カラム1</div><div style="background:#f3f4f6;padding:1rem">カラム2</div><div style="background:#f3f4f6;padding:1rem">カラム3</div></div>'],
            ['key' => 'acms-checklist', 'name' => 'チェックリスト', 'category' => 'content', 'description' => 'チェックマーク付きリスト', 'preview_html' => '<ul class="acms-checklist"><li>お悩み項目1</li><li>お悩み項目2</li><li>お悩み項目3</li></ul>'],
            ['key' => 'acms-flow', 'name' => 'フロー（ステップ）', 'category' => 'content', 'description' => '番号付きステップ表示', 'preview_html' => '<ol class="acms-flow"><li>カウンセリング</li><li>検査・診断</li><li>治療開始</li></ol>'],
            ['key' => 'acms-faq', 'name' => 'FAQ', 'category' => 'content', 'description' => 'よくある質問', 'preview_html' => '<dl class="acms-faq"><dt>治療期間はどのくらいですか？</dt><dd>通常3〜6ヶ月程度です。</dd><dt>痛みはありますか？</dt><dd>局所麻酔を行いますので、手術中の痛みはほとんどありません。</dd></dl>'],
            ['key' => 'acms-callout', 'name' => 'コールアウト', 'category' => 'content', 'description' => '強調ボックス', 'preview_html' => '<div class="acms-callout acms-callout--point"><strong>ポイント</strong><p>重要な情報をここに記載します。</p></div>'],
            ['key' => 'acms-cta', 'name' => 'CTA', 'category' => 'cta', 'description' => '予約・お問い合わせボタン', 'preview_html' => '<div class="acms-cta"><h2>ご予約・お問い合わせ</h2><p>お気軽にご連絡ください</p><a href="#">Web予約はこちら</a></div>'],
            ['key' => 'acms-note', 'name' => '注釈', 'category' => 'utility', 'description' => '小さい注釈テキスト', 'preview_html' => '<div class="acms-note">※ 料金はすべて税込表示です。</div>'],
        ];

        foreach ($components as $i => $c) {
            $c['sort_order'] = $i;
            Component::create($c);
        }
    }

    private function seedDemoPages(Site $site, User $admin): void
    {
        // TOP
        $top = Page::create([
            'site_id' => $site->id, 'slug' => '/', 'title' => 'デモ矯正歯科',
            'page_type' => 'top', 'sort_order' => 0, 'status' => 'ready',
        ]);
        $topGen = $this->createGeneration($top, '<div class="acms-section"><div class="container"><h2 class="acms-h2">あなたの笑顔を、もっと輝かせたい。</h2><p>デモ矯正歯科は、患者さま一人ひとりに寄り添った丁寧な矯正治療を心がけています。</p></div></div><div class="acms-section--alt"><div class="container"><h2 class="acms-h2">当院の特徴</h2><ul class="acms-checklist"><li>矯正専門医による精密な診断</li><li>痛みの少ない最新の矯正装置</li><li>丁寧なカウンセリングと治療計画</li></ul></div></div><div class="acms-cta"><h2>ご予約・お問い合わせ</h2><p>TEL: 03-1234-5678</p><a href="/contact">Web予約はこちら</a></div>');
        $top->update(['current_generation_id' => $topGen->id]);

        // インプラントページ
        $implant = Page::create([
            'site_id' => $site->id, 'slug' => '/implant', 'title' => 'インプラント',
            'page_type' => 'lower', 'treatment_key' => 'implant', 'sort_order' => 1, 'status' => 'ready',
        ]);
        $implantGen = $this->createGeneration($implant, '<div class="acms-page-header"><h1>インプラント</h1><p class="lead">安全・安心の最新技術で、天然歯に近い噛み心地を実現します。</p></div><div class="acms-section"><div class="container"><h2 class="acms-h2">インプラント治療とは</h2><p>歯を失った部分の顎の骨にチタン製の人工歯根を埋入し、その上に人工歯を被せる治療法です。</p></div></div><div class="acms-section--alt"><div class="container"><h2 class="acms-h2">治療の流れ</h2><ol class="acms-flow"><li>カウンセリング・CT撮影</li><li>治療計画の立案</li><li>インプラント埋入手術</li><li>治癒期間（3〜6ヶ月）</li><li>人工歯の装着</li></ol></div></div><div class="acms-section"><div class="container"><h2 class="acms-h2">よくあるご質問</h2><dl class="acms-faq"><dt>治療期間はどのくらいですか？</dt><dd>通常3〜6ヶ月程度です。骨の状態により異なります。</dd><dt>痛みはありますか？</dt><dd>局所麻酔を行いますので、手術中の痛みはほとんどありません。</dd></dl></div></div>');
        $implant->update(['current_generation_id' => $implantGen->id]);

        // 医院紹介
        $about = Page::create([
            'site_id' => $site->id, 'slug' => '/about', 'title' => '医院紹介',
            'page_type' => 'lower', 'sort_order' => 2, 'status' => 'ready',
        ]);
        $aboutGen = $this->createGeneration($about, '<div class="acms-page-header"><h1>医院紹介</h1></div><div class="acms-section"><div class="container"><h2 class="acms-h2">理念</h2><p>「すべての患者さまに、最善の歯科医療を」— これが私たちの変わらない信念です。</p></div></div><div class="acms-section--alt"><div class="container"><h2 class="acms-h2">設備紹介</h2><ul class="acms-checklist"><li>歯科用CT</li><li>マイクロスコープ</li><li>CAD/CAMシステム</li></ul></div></div>');
        $about->update(['current_generation_id' => $aboutGen->id]);
    }

    private function createGeneration(Page $page, string $html): PageGeneration
    {
        return PageGeneration::create([
            'page_id' => $page->id,
            'generation' => 1,
            'source' => 'manual',
            'content_html' => $html,
            'content_text' => strip_tags($html),
            'final_html' => $html,
            'status' => 'ready',
        ]);
    }
}
