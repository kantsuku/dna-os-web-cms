<?php

namespace Database\Seeders;

use App\Models\ContentVariant;
use App\Models\OverrideRule;
use App\Models\Page;
use App\Models\Section;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 管理ユーザー
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@dna-os.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $editor = User::create([
            'name' => '編集者',
            'email' => 'editor@dna-os.local',
            'password' => Hash::make('password'),
            'role' => 'editor',
        ]);

        // デモサイト
        $site = Site::create([
            'clinic_id' => 'demo_clinic_001',
            'name' => 'デモ歯科クリニック',
            'domain' => 'demo-dental.example.com',
            'template_set' => 'default',
            'status' => 'active',
        ]);

        $site->users()->attach([$admin->id, $editor->id]);

        // TOPページ
        $topPage = Page::create([
            'site_id' => $site->id,
            'slug' => '/',
            'title' => 'デモ歯科クリニック',
            'page_type' => 'top',
            'template_name' => 'default',
            'meta_description' => '地域密着の歯科医院です。インプラント・矯正・一般歯科に対応。',
            'status' => 'approved',
            'sort_order' => 0,
        ]);

        $this->createSectionWithContent($topPage, 'hero', 'dna_os', '<div class="hero-content"><h2>あなたの笑顔を、もっと輝かせたい。</h2><p>デモ歯科クリニックは、患者さま一人ひとりに寄り添った丁寧な治療を心がけています。</p><a href="/contact" class="btn">ご予約・お問い合わせ</a></div>', $admin);

        $this->createSectionWithContent($topPage, 'message', 'dna_os', '<h2>院長メッセージ</h2><p>当院は「痛くない、怖くない」をモットーに、最新の設備と技術で質の高い歯科治療を提供しております。お口のことでお悩みがありましたら、お気軽にご相談ください。</p><p class="doctor-name">院長 山田 太郎</p>', $admin);

        $this->createSectionWithContent($topPage, 'features', 'dna_os', '<h2>当院の特徴</h2><div class="feature-grid"><div class="feature"><h3>最新設備</h3><p>CTスキャン、マイクロスコープなど最新の医療機器を完備</p></div><div class="feature"><h3>痛みの少ない治療</h3><p>表面麻酔や電動注射器を用いた無痛治療</p></div><div class="feature"><h3>丁寧なカウンセリング</h3><p>治療前にしっかりとご説明し、ご納得いただいてから治療を開始</p></div></div>', $admin);

        $this->createSectionWithContent($topPage, 'treatments', 'dna_os', '<h2>診療科目</h2><ul><li><a href="/implant">インプラント</a></li><li><a href="/orthodontics">矯正歯科</a></li><li><a href="/general">一般歯科</a></li><li><a href="/preventive">予防歯科</a></li></ul>', $admin);

        $this->createSectionWithContent($topPage, 'cta', 'manual', '<h2>ご予約・お問い合わせ</h2><p>お気軽にお電話ください</p><p class="phone">TEL: 03-1234-5678</p><a href="/contact">Web予約はこちら</a>', $admin);

        // インプラントページ
        $implantPage = Page::create([
            'site_id' => $site->id,
            'slug' => '/implant',
            'title' => 'インプラント',
            'page_type' => 'lower',
            'template_name' => 'treatment',
            'meta_description' => '当院のインプラント治療について。安全・安心の最新技術で対応します。',
            'status' => 'approved',
            'sort_order' => 1,
        ]);

        $this->createSectionWithContent($implantPage, 'overview', 'dna_os', '<h2>インプラント治療とは</h2><p>インプラント治療は、歯を失った部分の顎の骨にチタン製の人工歯根を埋入し、その上に人工歯を被せる治療法です。天然歯に近い見た目と噛み心地を実現します。</p>', $admin);

        $this->createSectionWithContent($implantPage, 'features', 'dna_os', '<h2>当院のインプラント治療の特徴</h2><ul><li>CT撮影による精密診断</li><li>サージカルガイドによる安全な手術</li><li>10年保証制度</li></ul>', $admin);

        $this->createSectionWithContent($implantPage, 'price_table', 'manual', '<h2>料金表</h2><table><thead><tr><th>項目</th><th>料金（税込）</th></tr></thead><tbody><tr><td>インプラント1本（上部構造含む）</td><td>¥350,000〜</td></tr><tr><td>CT撮影・診断</td><td>¥15,000</td></tr><tr><td>骨造成（必要な場合）</td><td>¥50,000〜</td></tr></tbody></table>', $admin);

        $this->createSectionWithContent($implantPage, 'faq', 'dna_os', '<h2>よくあるご質問</h2><dl><dt>Q. 治療期間はどのくらいですか？</dt><dd>A. 通常3〜6ヶ月程度です。骨の状態により異なります。</dd><dt>Q. 痛みはありますか？</dt><dd>A. 局所麻酔を行いますので、手術中の痛みはほとんどありません。</dd></dl>', $admin);

        // 医院紹介ページ
        $aboutPage = Page::create([
            'site_id' => $site->id,
            'slug' => '/about',
            'title' => '医院紹介',
            'page_type' => 'lower',
            'template_name' => 'about',
            'meta_description' => 'デモ歯科クリニックの医院紹介。理念・設備・アクセス情報。',
            'status' => 'approved',
            'sort_order' => 2,
        ]);

        $this->createSectionWithContent($aboutPage, 'philosophy', 'dna_os', '<h2>理念</h2><p>「すべての患者さまに、最善の歯科医療を」— これが私たちの変わらない信念です。</p>', $admin);

        $this->createSectionWithContent($aboutPage, 'facility', 'dna_os', '<h2>設備紹介</h2><ul><li>歯科用CT</li><li>マイクロスコープ</li><li>CAD/CAMシステム</li><li>滅菌設備（クラスB）</li></ul>', $admin);
    }

    private function createSectionWithContent(Page $page, string $key, string $sourceType, string $html, User $user): void
    {
        $section = Section::create([
            'page_id' => $page->id,
            'section_key' => $key,
            'sort_order' => Section::where('page_id', $page->id)->count(),
            'content_source_type' => $sourceType,
        ]);

        OverrideRule::create([
            'section_id' => $section->id,
            'policy' => $sourceType === 'manual' ? 'manual_only' : 'auto_sync',
            'reason' => '初期設定',
            'set_by' => $user->id,
        ]);

        ContentVariant::create([
            'section_id' => $section->id,
            'version' => 1,
            'source_type' => $sourceType === 'dna_os' ? 'dna_os_sync' : 'human_edit',
            'content_html' => $html,
            'content_raw' => strip_tags($html),
            'original_content' => $html,
            'edited_by' => $user->id,
            'status' => 'approved',
        ]);
    }
}
