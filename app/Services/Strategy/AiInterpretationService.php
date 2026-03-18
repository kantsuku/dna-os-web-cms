<?php

namespace App\Services\Strategy;

use App\Models\FreeInputRequest;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiInterpretationService
{
    /**
     * フリー入力テキストをAIで解釈し、対象ページ・セクション・アクションを特定する
     */
    public function interpret(FreeInputRequest $freeInput): array
    {
        $site = $freeInput->site;
        if (!$site) {
            return $this->fallbackInterpretation($freeInput->raw_text);
        }

        $pages = $site->pages()->select('id', 'slug', 'title', 'page_type')->get();
        $pagesList = $pages->map(fn($p) => "{$p->slug}: {$p->title} ({$p->page_type})")->implode("\n");

        // Claude API 呼び出し
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('Anthropic APIキーが未設定のためフォールバック');
            return $this->fallbackInterpretation($freeInput->raw_text);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-sonnet-4-20250514',
                    'max_tokens' => 1024,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($freeInput->raw_text, $pagesList),
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI解釈API失敗', ['status' => $response->status()]);
                return $this->fallbackInterpretation($freeInput->raw_text);
            }

            $text = $response->json('content.0.text', '');
            return $this->parseAiResponse($text);
        } catch (\Throwable $e) {
            Log::error('AI解釈エラー', ['error' => $e->getMessage()]);
            return $this->fallbackInterpretation($freeInput->raw_text);
        }
    }

    /**
     * 解釈結果をFreeInputRequestに保存する
     */
    public function interpretAndSave(FreeInputRequest $freeInput): FreeInputRequest
    {
        $interpretation = $this->interpret($freeInput);

        $freeInput->update([
            'ai_interpretation' => $interpretation,
            'interpretation_status' => 'interpreted',
        ]);

        return $freeInput;
    }

    private function buildPrompt(string $rawText, string $pagesList): string
    {
        return <<<PROMPT
あなたはWeb修正リクエストの解釈AIです。
ユーザーの自然言語リクエストを解析し、以下をJSON形式で返してください：

{
  "target_page_slug": "対象ページのslug（不明なら null）",
  "target_section": "対象セクション名やID（不明なら null）",
  "action": "テキスト変更 / 画像差替 / レイアウト変更 / 削除 / 追加 / メタ情報変更",
  "task_type": "update_content / update_meta / new_page / delete_page / check_quality",
  "description": "解釈内容の簡潔な説明",
  "confidence": 0.0〜1.0
}

サイトのページ一覧：
{$pagesList}

ユーザーのリクエスト：
{$rawText}

JSONのみを返してください。
PROMPT;
    }

    private function parseAiResponse(string $text): array
    {
        // JSONを抽出
        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }
        return $this->fallbackInterpretation($text);
    }

    private function fallbackInterpretation(string $rawText): array
    {
        return [
            'target_page_slug' => null,
            'target_section' => null,
            'action' => '不明',
            'task_type' => 'update_content',
            'description' => "自動解釈できませんでした。元のテキスト: {$rawText}",
            'confidence' => 0.0,
        ];
    }
}
