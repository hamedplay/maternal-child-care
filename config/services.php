<?php

/**
 * تنظیمات سرویس‌های بیرونی. تمام Secretها فقط از Environment Variable خوانده می‌شوند.
 */
return [
    // kavenegar | bale
    'otp_provider' => strtolower((string) (getenv('OTP_PROVIDER') ?: 'kavenegar')),

    'kavenegar' => [
        'api_key' => getenv('KAVENEGAR_API_KEY') ?: '',
        'otp_template' => getenv('KAVENEGAR_OTP_TEMPLATE') ?: 'verify',
        'sender' => getenv('KAVENEGAR_SENDER') ?: '',
    ],

    'bale' => [
        // Endpoint و Token رسمی که هاشمی از سرویس بله دریافت می‌کند.
        'otp_endpoint' => getenv('BALE_OTP_ENDPOINT') ?: '',
        'api_token' => getenv('BALE_OTP_TOKEN') ?: '',
        'otp_template' => getenv('BALE_OTP_TEMPLATE') ?: '',
    ],

    'otp_pepper' => getenv('OTP_PEPPER') ?: 'CHANGE-THIS-TO-A-LONG-RANDOM-SECRET',
    'reminder_cron_secret' => getenv('REMINDER_CRON_SECRET') ?: '',
];
