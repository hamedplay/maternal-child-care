<?php

namespace App\Services;

/** ارتباط مستقیم با OpenAI API. */
class LlmService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $config = require BASE_PATH . '/config/ai.php';
        $this->apiKey = (string) ($config['openai_api_key'] ?? '');
        $this->model = (string) ($config['model'] ?? 'gpt-4o-mini');
    }

    public function ask(string $question, array $contextBlocks = [], array $history = []): string
    {
        if ($this->apiKey === '') {
            error_log('LlmService: OPENAI_API_KEY تنظیم نشده.');
            return 'سرویس هوش مصنوعی هنوز پیکربندی نشده. لطفاً بعداً دوباره امتحان کنید.';
        }

        $messages = [['role' => 'system', 'content' => $this->buildSystemPrompt($contextBlocks)]];
        foreach ($history as $msg) {
            if (!isset($msg['role'], $msg['content'])) {
                continue;
            }
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        $payload = json_encode([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => 700,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('LlmService خطای اتصال: ' . $curlError);
            return 'در ارتباط با سرویس هوش مصنوعی مشکلی پیش اومد. لطفاً دوباره امتحان کنید.';
        }

        $data = json_decode((string) $response, true);
        if ($httpCode < 200 || $httpCode >= 300 || !isset($data['choices'][0]['message']['content'])) {
            error_log('LlmService پاسخ نامعتبر (HTTP ' . $httpCode . '): ' . (string) $response);
            return 'پاسخ نامعتبری از سرویس هوش مصنوعی دریافت شد. لطفاً دوباره تلاش کنید.';
        }

        return trim((string) $data['choices'][0]['message']['content']);
    }

    private function buildSystemPrompt(array $contextBlocks): string
    {
        $prompt = "تو یک دستیار هوشمند در وب‌سایت «مراقبت مادر و کودک» هستی. وظیفه‌ات پاسخ‌دادن به سوالات کاربران درباره بارداری، نوزاد و کودک است.\n\n"
            . "قوانین مهم:\n"
            . "۱. همیشه فارسی، روشن و کوتاه پاسخ بده.\n"
            . "۲. اگر اطلاعات مرجع بازیابی‌شده مرتبط بود، پاسخ را بر پایه همان اطلاعات بساز.\n"
            . "۳. جایگزین پزشک نیستی؛ علائم نگران‌کننده یا اورژانسی را برای مراجعه فوری ارجاع بده.\n"
            . "۴. دوز دقیق دارو یا مکمل تجویز نکن.\n"
            . "۵. اگر داده کافی نداری، صادقانه اعلام کن.\n"
            . "۶. استانداردهای رشد و تکامل میانگین آماری هستند و تشخیص قطعی با پزشک است.\n";

        if ($contextBlocks) {
            $prompt .= "\n--- اطلاعات مرجع بازیابی‌شده ---\n";
            foreach ($contextBlocks as $block) {
                $prompt .= '- ' . $block . "\n";
            }
            $prompt .= "--- پایان اطلاعات مرجع ---\n";
        }
        return $prompt;
    }
}
