<?php

namespace App\Services;

interface OtpSenderInterface
{
    public function isConfigured(): bool;

    public function sendOtp(string $phone, string $code): bool;
}
