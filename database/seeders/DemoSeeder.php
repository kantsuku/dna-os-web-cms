<?php

namespace Database\Seeders;

use App\Models\Component;
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

        $this->seedDesignTokens();
        $this->seedComponents();

        $site = Site::create([
            'clinic_id' => 'demo_clinic_001',
            'name' => 'デモ矯正歯科',
            'domain' => 'demo-ortho.example.com',
            'status' => 'active',
        ]);

        $design = SiteDesign::create([
            'site_id' => $site->id,
            'name' => 'color1（ブルーグレー）',
            'tokens' => [
                'color-main' => '#DDDDDC',
                'color-main2' => '#2793EA',
                'color-sub' => '#406C30',
            ],
            'status' => 'active',
        ]);

        $site->update(['design_id' => $design->id]);
        $site->users()->attach($admin->id);

        $this->seedDemoPages($site);
    }

    private function seedDesignTokens(): void
    {
        // 既存テーマ _variables.scss のカラー体系に合わせる
        $tokens = [
            // カラー（パターン1: ブルーグレー）
            ['category' => 'color', 'key' => 'color-main', 'value' => '#DDDDDC', 'label' => 'メインカラー'],
            ['category' => 'color', 'key' => 'color-main2', 'value' => '#2793EA', 'label' => 'メインカラー2'],
            ['category' => 'color', 'key' => 'color-main-light', 'value' => '#DAC7AF', 'label' => 'メイン（淡）'],
            ['category' => 'color', 'key' => 'color-main-light2', 'value' => '#dbeaf7', 'label' => 'メイン（淡2）'],
            ['category' => 'color', 'key' => 'color-main-dark', 'value' => '#B28247', 'label' => 'メイン（暗）'],
            ['category' => 'color', 'key' => 'color-main2-light', 'value' => '#95c9f3', 'label' => 'メイン2（淡）'],
            ['category' => 'color', 'key' => 'color-main2-dark', 'value' => '#0057a1', 'label' => 'メイン2（暗）'],
            ['category' => 'color', 'key' => 'color-sub', 'value' => '#406C30', 'label' => 'サブカラー'],
            ['category' => 'color', 'key' => 'color-sub-light', 'value' => '#f5e6c9', 'label' => 'サブ（淡）'],
            ['category' => 'color', 'key' => 'color-sub-dark', 'value' => '#8a6333', 'label' => 'サブ（暗）'],
            ['category' => 'color', 'key' => 'color-gray-white', 'value' => '#f5f5f5', 'label' => 'グレー白'],
            ['category' => 'color', 'key' => 'color-gray-light', 'value' => '#F5F5F5', 'label' => 'グレー（淡）'],
            ['category' => 'color', 'key' => 'color-gray', 'value' => '#B5B2AD', 'label' => 'グレー'],
            ['category' => 'color', 'key' => 'color-gray-dark', 'value' => '#64625A', 'label' => 'グレー（暗）'],
            ['category' => 'color', 'key' => 'color-gray-black', 'value' => '#343330', 'label' => 'グレー黒'],
            ['category' => 'color', 'key' => 'color-white', 'value' => '#FFFFFF', 'label' => '白'],
            ['category' => 'color', 'key' => 'color-black', 'value' => '#000000', 'label' => '黒'],
            ['category' => 'color', 'key' => 'color-link', 'value' => '#2DABF1', 'label' => 'リンク色'],
            ['category' => 'color', 'key' => 'color-error', 'value' => '#ea6565', 'label' => 'エラー色'],
            // フォント
            ['category' => 'font', 'key' => 'font-primary', 'value' => "'Noto Sans JP', sans-serif", 'label' => '本文フォント'],
            ['category' => 'font', 'key' => 'font-secondary', 'value' => "'Noto Serif JP', serif", 'label' => '見出しフォント'],
            ['category' => 'font', 'key' => 'font-en', 'value' => "'Reddit Sans', sans-serif", 'label' => '英数字フォント'],
            // レイアウト
            ['category' => 'layout', 'key' => 'content-width', 'value' => '1100px', 'label' => 'コンテンツ幅'],
            ['category' => 'layout', 'key' => 'container-width', 'value' => '1200px', 'label' => 'コンテナ幅'],
            ['category' => 'layout', 'key' => 'wrapper-width', 'value' => '1512px', 'label' => 'ラッパー幅'],
            // スペーシング
            ['category' => 'spacing', 'key' => 'section-padding-sp', 'value' => '60px', 'label' => 'セクション間隔（SP）'],
            ['category' => 'spacing', 'key' => 'section-padding-pc', 'value' => '80px', 'label' => 'セクション間隔（PC）'],
        ];

        foreach ($tokens as $i => $t) {
            $t['sort_order'] = $i;
            DesignToken::create($t);
        }
    }

    private function seedComponents(): void
    {
        $components = [
            // レイアウト
            ['key' => 'com-section', 'migration_key' => 'acms-section', 'name' => 'セクション', 'category' => 'layout', 'description' => '上下余白付きセクション', 'variants' => ['pt0', 'pb0']],
            ['key' => 'com-contentWidth', 'migration_key' => 'acms-container', 'name' => 'コンテンツ幅', 'category' => 'layout', 'description' => 'max-width制限ラッパー', 'variants' => ['__1150', '__1200', '__1400']],
            ['key' => 'com-bgc-gray-white', 'migration_key' => 'acms-section--alt', 'name' => 'セクション（グレー背景）', 'category' => 'layout', 'description' => 'グレー背景の交互セクション'],
            ['key' => 'com-col2', 'migration_key' => 'acms-grid--col-2', 'name' => '2カラム', 'category' => 'layout', 'description' => '2カラムグリッド', 'variants' => ['_circle', '_gap_40']],
            ['key' => 'com-col3', 'migration_key' => 'acms-grid--col-3', 'name' => '3カラム', 'category' => 'layout', 'description' => '3カラムグリッド', 'variants' => ['_circle', '_gap_40', '_gap_30']],
            ['key' => 'com-col4', 'migration_key' => 'acms-grid--col-4', 'name' => '4カラムリンク', 'category' => 'layout', 'description' => '4カラムのリンクリスト', 'variants' => ['_no-hover']],
            ['key' => 'com-col-img_right', 'migration_key' => 'acms-media--right', 'name' => '画像右レイアウト', 'category' => 'layout', 'description' => 'テキスト左・画像右', 'variants' => ['_md', '_lg']],
            ['key' => 'com-col-img_left', 'migration_key' => 'acms-media--left', 'name' => '画像左レイアウト', 'category' => 'layout', 'description' => 'テキスト右・画像左', 'variants' => ['_md', '_lg']],
            // 見出し
            ['key' => 'com-h1', 'migration_key' => 'acms-h1', 'name' => 'H1見出し', 'category' => 'heading', 'description' => 'ページ最上位見出し'],
            ['key' => 'com-h2', 'migration_key' => 'acms-h2', 'name' => 'H2見出し', 'category' => 'heading', 'description' => 'セクション見出し（左ボーダー+背景帯）'],
            ['key' => 'com-h2-top', 'migration_key' => 'acms-page-header', 'name' => 'ページ冒頭H2', 'category' => 'heading', 'description' => 'ページ冒頭の大見出し+リード文'],
            ['key' => 'com-h3', 'migration_key' => 'acms-h3', 'name' => 'H3見出し', 'category' => 'heading', 'description' => 'サブ見出し（左ボーダー）', 'variants' => ['com-underline', '_no-leftline']],
            ['key' => 'com-h4', 'migration_key' => 'acms-h4', 'name' => 'H4見出し', 'category' => 'heading', 'description' => 'シンプルなH4'],
            ['key' => 'com-h4-dot', 'migration_key' => 'acms-h4--dot', 'name' => 'H4見出し（ドット）', 'category' => 'heading', 'description' => 'ドットマーカー付きH4'],
            ['key' => 'com-h4-icon', 'migration_key' => 'acms-h4--icon', 'name' => 'H4見出し（アイコン）', 'category' => 'heading', 'description' => 'SVGアイコン付きH4'],
            ['key' => 'com-title', 'migration_key' => 'acms-section-title', 'name' => 'セクションタイトル', 'category' => 'heading', 'description' => '英語サブタイトル+日本語メインタイトル', 'variants' => ['_white']],
            // コンテンツ
            ['key' => 'com-ul-check_03', 'migration_key' => 'acms-checklist', 'name' => 'チェックリスト', 'category' => 'content', 'description' => 'チェックマーク付きリスト', 'variants' => ['_white']],
            ['key' => 'com-ul-check', 'migration_key' => 'acms-checklist--svg', 'name' => 'チェックリスト（SVG）', 'category' => 'content', 'description' => 'SVGチェックマーク付き'],
            ['key' => 'com-ul-check_02', 'migration_key' => 'acms-checklist--detail', 'name' => 'チェックリスト（詳細）', 'category' => 'content', 'description' => 'チェック+見出し+説明', 'variants' => ['_white']],
            ['key' => 'com-ul-dot', 'migration_key' => 'acms-list--dot', 'name' => 'ドットリスト', 'category' => 'content', 'description' => '丸ドット付きリスト'],
            ['key' => 'com-ul-note', 'migration_key' => 'acms-list--note', 'name' => '注釈リスト', 'category' => 'content', 'description' => '※マーク付き注釈リスト'],
            ['key' => 'com-ul-num01', 'migration_key' => 'acms-numbered--titled', 'name' => 'ナンバーリスト（タイトル付）', 'category' => 'content', 'description' => '自動連番+見出し+説明', 'variants' => ['_white']],
            ['key' => 'com-ul-num02', 'migration_key' => 'acms-numbered', 'name' => 'ナンバーリスト', 'category' => 'content', 'description' => '自動連番リスト', 'variants' => ['_white']],
            ['key' => 'com-flow', 'migration_key' => 'acms-flow', 'name' => 'フロー', 'category' => 'content', 'description' => 'ステップフロー（矢印コネクタ付き）', 'variants' => ['_white']],
            ['key' => 'com-faq', 'migration_key' => 'acms-faq', 'name' => 'FAQ', 'category' => 'content', 'description' => 'Q&Aリスト', 'variants' => ['_white']],
            ['key' => 'com-point', 'migration_key' => 'acms-callout--point', 'name' => 'ポイントブロック', 'category' => 'content', 'description' => '画像+テキストの強調ブロック', 'variants' => ['_right']],
            ['key' => 'com-comment', 'migration_key' => 'acms-callout--comment', 'name' => 'コメントブロック', 'category' => 'content', 'description' => '人物アイコン+吹き出しコメント', 'variants' => ['_right']],
            ['key' => 'com-note', 'migration_key' => 'acms-note', 'name' => '注釈ブロック', 'category' => 'content', 'description' => '注釈テキストの囲み'],
            ['key' => 'com-index', 'migration_key' => 'acms-toc', 'name' => '目次', 'category' => 'content', 'description' => 'ページ内目次'],
            ['key' => 'com-table01', 'migration_key' => 'acms-table--basic', 'name' => '表組み（基本）', 'category' => 'content', 'description' => '定義型テーブル'],
            ['key' => 'com-table02', 'migration_key' => 'acms-table--price', 'name' => '表組み（料金表）', 'category' => 'content', 'description' => '料金表（画像付き）'],
            ['key' => 'com-timeline', 'migration_key' => 'acms-timeline', 'name' => '年表', 'category' => 'content', 'description' => '経歴・年表'],
            ['key' => 'com-img_switch', 'migration_key' => 'acms-before-after', 'name' => 'Before/After', 'category' => 'content', 'description' => '画像比較スライダー'],
            ['key' => 'com-risk-note', 'migration_key' => 'acms-risk-note', 'name' => 'リスク注意書き', 'category' => 'content', 'description' => '自由診療のリスク・副作用表示'],
            // CTA
            ['key' => 'com-box03', 'migration_key' => 'acms-cta', 'name' => 'お問い合わせボックス', 'category' => 'cta', 'description' => '電話・WEB予約のCTAボックス'],
            ['key' => 'com-btn01', 'migration_key' => 'acms-btn--primary', 'name' => 'ボタン（プライマリ）', 'category' => 'cta', 'description' => 'メインボタン', 'variants' => ['_line', '_large']],
            ['key' => 'com-btn02', 'migration_key' => 'acms-btn--arrow', 'name' => 'ボタン（矢印付き）', 'category' => 'cta', 'description' => '矢印付きリンクボタン', 'variants' => ['_white', '_border']],
            ['key' => 'com-btn03', 'migration_key' => 'acms-btn--secondary', 'name' => 'ボタン（セカンダリ）', 'category' => 'cta', 'description' => 'サブボタン', 'variants' => ['_white']],
            // ユーティリティ
            ['key' => 'com-spacer-section', 'migration_key' => 'acms-spacer--xl', 'name' => 'スペーサー（大）', 'category' => 'utility', 'description' => 'セクション間余白'],
            ['key' => 'com-spacer-large', 'migration_key' => 'acms-spacer--lg', 'name' => 'スペーサー（中大）', 'category' => 'utility', 'description' => '大きめの余白'],
            ['key' => 'com-spacer-medium', 'migration_key' => 'acms-spacer--md', 'name' => 'スペーサー（中）', 'category' => 'utility', 'description' => '標準の余白'],
            ['key' => 'com-spacer-small', 'migration_key' => 'acms-spacer--sm', 'name' => 'スペーサー（小）', 'category' => 'utility', 'description' => '小さめの余白'],
            ['key' => 'com-img', 'migration_key' => 'acms-img', 'name' => 'レスポンシブ画像', 'category' => 'utility', 'description' => '幅100%+角丸の画像'],
            ['key' => 'com-underline', 'migration_key' => 'acms-divider', 'name' => '区切り線', 'category' => 'utility', 'description' => '区切り罫線'],
            ['key' => 'com-schedule', 'migration_key' => 'acms-schedule', 'name' => '診療スケジュール', 'category' => 'content', 'description' => '診療時間・休診日の表'],
        ];

        foreach ($components as $i => $c) {
            $c['sort_order'] = $i;
            Component::create($c);
        }
    }

    private function seedDemoPages(Site $site): void
    {
        // TOP
        $top = Page::create(['site_id' => $site->id, 'slug' => '/', 'title' => 'デモ矯正歯科', 'page_type' => 'top', 'sort_order' => 0, 'status' => 'ready']);
        $topGen = $this->gen($top, '<section class="com-section"><div class="com-contentWidth"><div class="com-title"><p class="com-title-en">INFORMATION</p><h2 class="com-title-jp">あなたの笑顔を、もっと輝かせたい。</h2></div><p>デモ矯正歯科は、患者さま一人ひとりに寄り添った丁寧な矯正治療を心がけています。</p></div></section><section class="com-section com-bgc-gray-white"><div class="com-contentWidth"><h2 class="com-h2">当院の特徴</h2><ul class="com-ul-check_03"><li><div class="com-ul-check_03-item"><p>矯正専門医による精密な診断</p></div></li><li><div class="com-ul-check_03-item"><p>痛みの少ない最新の矯正装置</p></div></li><li><div class="com-ul-check_03-item"><p>丁寧なカウンセリングと治療計画</p></div></li></ul></div></section>');
        $top->update(['current_generation_id' => $topGen->id]);

        // インプラント
        $impl = Page::create(['site_id' => $site->id, 'slug' => '/implant', 'title' => 'インプラント', 'page_type' => 'lower', 'treatment_key' => 'implant', 'sort_order' => 1, 'status' => 'ready']);
        $implGen = $this->gen($impl, '<h2 class="com-h2-top">インプラント<span class="com-h2-top-desc">安全・安心の最新技術で、天然歯に近い噛み心地を実現します。</span></h2><section class="com-section"><div class="com-contentWidth"><h2 class="com-h2">インプラント治療とは</h2><p>歯を失った部分の顎の骨にチタン製の人工歯根を埋入し、その上に人工歯を被せる治療法です。</p></div></section><section class="com-section com-bgc-gray-white"><div class="com-contentWidth"><h2 class="com-h2">治療の流れ</h2><div class="com-flow"><div class="com-flow-item"><div class="com-flow-item-step"><p>STEP 01</p></div><div class="com-flow-item-text"><h3>カウンセリング・CT撮影</h3></div></div><div class="com-flow-item"><div class="com-flow-item-step"><p>STEP 02</p></div><div class="com-flow-item-text"><h3>治療計画の立案</h3></div></div><div class="com-flow-item"><div class="com-flow-item-step"><p>STEP 03</p></div><div class="com-flow-item-text"><h3>インプラント埋入手術</h3></div></div></div></div></section><section class="com-section"><div class="com-contentWidth"><h2 class="com-h2">よくあるご質問</h2><div class="com-faq"><ul class="com-faq-list"><li class="com-faq-list-item"><div class="com-faq-list-item-q"><h4>治療期間はどのくらいですか？</h4></div><div class="com-faq-list-item-a"><p>通常3〜6ヶ月程度です。骨の状態により異なります。</p></div></li><li class="com-faq-list-item"><div class="com-faq-list-item-q"><h4>痛みはありますか？</h4></div><div class="com-faq-list-item-a"><p>局所麻酔を行いますので、手術中の痛みはほとんどありません。</p></div></li></ul></div></div></section>');
        $impl->update(['current_generation_id' => $implGen->id]);

        // 医院紹介
        $about = Page::create(['site_id' => $site->id, 'slug' => '/about', 'title' => '医院紹介', 'page_type' => 'lower', 'sort_order' => 2, 'status' => 'ready']);
        $aboutGen = $this->gen($about, '<h2 class="com-h2-top">医院紹介</h2><section class="com-section"><div class="com-contentWidth"><h2 class="com-h2">理念</h2><p>「すべての患者さまに、最善の歯科医療を」— これが私たちの変わらない信念です。</p></div></section><section class="com-section com-bgc-gray-white"><div class="com-contentWidth"><h2 class="com-h2">設備紹介</h2><ul class="com-ul-check_03"><li><div class="com-ul-check_03-item"><p>歯科用CT</p></div></li><li><div class="com-ul-check_03-item"><p>マイクロスコープ</p></div></li><li><div class="com-ul-check_03-item"><p>CAD/CAMシステム</p></div></li></ul></div></section>');
        $about->update(['current_generation_id' => $aboutGen->id]);
    }

    private function gen(Page $page, string $html): PageGeneration
    {
        return PageGeneration::create([
            'page_id' => $page->id, 'generation' => 1, 'source' => 'manual',
            'content_html' => $html, 'content_text' => strip_tags($html),
            'final_html' => $html, 'status' => 'ready',
        ]);
    }
}
