<?php

namespace App\Services;

/**
 * اتصال OTP بله.
 * Endpoint رسمی OTP باید از پنل/قرارداد بله دریافت شود، بنابراین URL و Token از env خوانده می‌شوند.
 * قرارداد مورد انتظار: POST JSON با phone/code/template و Bearer token اختیاری.
 */
class BaleOtpService implements OtpSenderInterface
{
    private string $endpoint;
    private string $token;
    private string $template;

    public function __construct()
    {
        $config = require BASE_PATH . '/config/services.php';
        $this->endpoint = trim((string) ($config['bale']['otp_endpoint'] ?? ''));
        $this->token = trim((string) ($config['bale']['api_token'] ?? ''));
        $this->template = trim((string) ($config['bale']['otp_template'] ?? ''));
    }

    public function isConfigured(): bool
    {
        return $this->endpoint !== '';
    }

    public function sendOtp(string $phone, string $code): bool
    {
        if (!$this->isConfigured()) {
            error_log("[BaleOtpService] DEV MODE - کد {$phone}: {$code}");
            return true;
        }

        $payload = [
            'phone' => $phone,
            'code' => $code,
        ];
        if ($this->template !== '') {
            $payload['template'] = $this->template;
        }

        $headers = ['Content-Type: application/json'];
        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300) {
            error_log('[BaleOtpService] ارسال ناموفق. HTTP=' . $httpCode . ' error=' . $curlError . ' response=' . (string) $response);
            return false;
        }

        return true;
    }
}
