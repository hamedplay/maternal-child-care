<?php
require_once __DIR__ . '/../config/config.php';

use App\Repositories\SupportMessageRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone_number'] ?? ''));
$type = (string) ($_POST['message_type'] ?? 'question');
$message = trim((string) ($_POST['message'] ?? ''));
$returnTo = basename(parse_url((string) ($_POST['return_to'] ?? 'index.php'), PHP_URL_PATH) ?: 'index.php');
if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/', $returnTo)) {
    $returnTo = 'index.php';
}

$validType = in_array($type, ['bug', 'question', 'suggestion'], true);
$validEmail = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
$validPhone = $phone !== '' && preg_match('/^09\d{9}$/', $phone);

if ($message === '' || !$validType || (!$validEmail && !$validPhone)) {
    header('Location: ' . $returnTo . '?support_error=1');
    exit;
}

$ok = (new SupportMessageRepository())->create([
    'full_name' => $fullName !== '' ? $fullName : null,
    'email' => $validEmail ? $email : null,
    'phone_number' => $validPhone ? $phone : null,
    'message_type' => $type,
    'message' => $message,
]);

header('Location: ' . $returnTo . ($ok ? '?support_sent=1' : '?support_error=1'));
exit;
