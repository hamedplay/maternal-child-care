<?php

namespace App\Services;

class KavenegarService implements OtpSenderInterface
{
    private string $apiKey;
    private string $template;
    private string $sender;

    public function __construct()
    {
        $config = require BASE_PATH . '/config/services.php';
        $this->apiKey = $config['kavenegar']['api_key'];
        $this->template = $config['kavenegar']['otp_template'];
        $this->sender = (string) ($config['kavenegar']['sender'] ?? '');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function sendOtp(string $phone, string $code): bool
    {
        if ($this->apiKey === '') {
            error_log("[KavenegarService] DEV MODE (بدون API key) - کد {$phone}: {$code}");
            return true;
        }

        $url = sprintf(
            'https://api.kavenegar.com/v1/%s/verify/lookup.json?%s',
            $this->apiKey,
            http_build_query([
                'receptor' => $phone,
                'token' => $code,
                'template' => $this->template,
            ])
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            error_log('[KavenegarService] خطای cURL: ' . $curlError);
            return false;
        }

        $data = json_decode($response, true);
        $status = $data['return']['status'] ?? null;

        if ($httpCode !== 200 || $status !== 200) {
            error_log('[KavenegarService] ارسال ناموفق: ' . $response);
            return false;
        }

        return true;
    }

    public function sendMessage(string $phone, string $message): bool
    {
        if ($this->apiKey === '' || $this->sender === '') {
            error_log('[KavenegarService] ارسال یادآوری غیرفعال است؛ API key یا sender تنظیم نشده.');
            return false;
        }

        $url = sprintf('https://api.kavenegar.com/v1/%s/sms/send.json?%s', $this->apiKey, http_build_query([
            'receptor' => $phone,
            'sender' => $this->sender,
            'message' => $message,
        ]));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '' || $httpCode !== 200) {
            error_log('[KavenegarService] خطای ارسال پیام: ' . $error . ' response=' . (string) $response);
            return false;
        }

        $data = json_decode((string) $response, true);
        return (int) ($data['return']['status'] ?? 0) === 200;
    }
}
