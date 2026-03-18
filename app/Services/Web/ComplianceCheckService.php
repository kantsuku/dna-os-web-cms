<?php

namespace App\Services\Web;

use App\Models\ExceptionContent;

class ComplianceCheckService
{
    /**
     * 医療広告GL準拠の必須項目
     */
    private const CASE_REQUIRED_FIELDS = [
        'chief_complaint' => '主訴',
        'treatment' => '治療内容',
        'duration' => '治療期間',
        'cost' => '費用',
        'risks' => 'リスク・副作用',
    ];

    /**
     * 禁止表現リスト
     */
    private const PROHIBITED_TERMS = [
        '無痛', '絶対に', '必ず治る', '100%', '完全', '最短',
        '日本一', 'No.1', '最高', '世界初', '業界初',
        '絶対安全', '副作用なし', 'リスクなし', '痛みゼロ',
    ];

    /**
     * 例外コンテンツのコンプライアンスチェックを実行する
     */
    public function check(ExceptionContent $content): array
    {
        $results = [];

        // 症例コンテンツの場合
        if (in_array($content->content_type, ['case_study', 'case'])) {
            $results = array_merge($results, $this->checkCaseStudy($content));
        }

        // 全タイプ共通チェック
        $results = array_merge($results, $this->checkProhibitedTerms($content));
        $results = array_merge($results, $this->checkContentLength($content));

        // 結果を保存
        $content->update(['compliance_check' => $results]);

        return $results;
    }

    /**
     * チェック結果にNGが含まれるか
     */
    public function hasErrors(array $results): bool
    {
        return collect($results)->contains(fn($r) => $r['status'] === 'ng');
    }

    /**
     * チェック結果の警告数
     */
    public function warningCount(array $results): int
    {
        return collect($results)->where('status', 'warning')->count();
    }

    /**
     * 症例コンテンツの必須項目チェック
     */
    private function checkCaseStudy(ExceptionContent $content): array
    {
        $results = [];
        $data = $content->structured_data ?? [];

        foreach (self::CASE_REQUIRED_FIELDS as $field => $label) {
            $value = $data[$field] ?? '';
            if (empty(trim($value))) {
                $results[] = [
                    'check' => "必須項目: {$label}",
                    'status' => 'ng',
                    'message' => "「{$label}」が未入力です。医療広告GLに基づき、必ず記載してください。",
                    'gl_reference' => '医療広告ガイドライン 第4-1-(2)',
                ];
            } else {
                $results[] = [
                    'check' => "必須項目: {$label}",
                    'status' => 'ok',
                    'message' => '記載あり',
                ];
            }
        }

        // 画像チェック
        $images = $data['images'] ?? [];
        $hasBefore = collect($images)->contains(fn($img) => ($img['type'] ?? '') === 'before');
        $hasAfter = collect($images)->contains(fn($img) => ($img['type'] ?? '') === 'after');

        if (!$hasBefore || !$hasAfter) {
            $results[] = [
                'check' => '術前/術後画像',
                'status' => 'warning',
                'message' => '術前・術後の画像が不足しています。症例ページでは両方の掲載が推奨されます。',
            ];
        }

        return $results;
    }

    /**
     * 禁止表現チェック
     */
    private function checkProhibitedTerms(ExceptionContent $content): array
    {
        $results = [];
        $text = strip_tags($content->content_html ?? '');

        $found = [];
        foreach (self::PROHIBITED_TERMS as $term) {
            if (mb_strpos($text, $term) !== false) {
                $found[] = $term;
            }
        }

        if (!empty($found)) {
            $results[] = [
                'check' => '禁止表現',
                'status' => 'ng',
                'message' => '以下の禁止表現が含まれています: ' . implode(', ', $found),
                'found_terms' => $found,
                'gl_reference' => '医療広告ガイドライン 第3-1',
            ];
        } else {
            $results[] = [
                'check' => '禁止表現',
                'status' => 'ok',
                'message' => '禁止表現は検出されませんでした',
            ];
        }

        return $results;
    }

    /**
     * コンテンツ長チェック
     */
    private function checkContentLength(ExceptionContent $content): array
    {
        $text = strip_tags($content->content_html ?? '');
        $length = mb_strlen($text);
        $results = [];

        if ($length < 50) {
            $results[] = [
                'check' => 'コンテンツ量',
                'status' => 'warning',
                'message' => "コンテンツが短すぎます（{$length}文字）。最低100文字以上を推奨します。",
            ];
        }

        return $results;
    }
}
