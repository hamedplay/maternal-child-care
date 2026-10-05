<?php

require_once __DIR__ . '/../config/config.php';

use App\Repositories\AiChatRepository;
use App\Repositories\AiKnowledgeRepository;
use App\Repositories\ArticleRecommendationRepository;
use App\Services\LlmService;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'روش درخواست مجاز نیست.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$question = trim((string) ($input['question'] ?? ''));
if ($question === '' || mb_strlen($question) > 500) {
    http_response_code(422);
    echo json_encode(['error' => $question === '' ? 'لطفاً سوال خود را بنویسید.' : 'سوال شما خیلی طولانیه.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$aiConfig = require BASE_PATH . '/config/ai.php';
$maxHistory = (int) ($aiConfig['max_history_messages'] ?? 12);
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$chatRepository = new AiChatRepository();

if ($userId) {
    $recentHistory = $chatRepository->recentMessages($userId, $maxHistory);
    $pastQuestions = $chatRepository->recentUserQuestions($userId, 5);
} else {
    $_SESSION['ai_chat_history'] = isset($_SESSION['ai_chat_history']) && is_array($_SESSION['ai_chat_history']) ? $_SESSION['ai_chat_history'] : [];
    $recentHistory = array_slice($_SESSION['ai_chat_history'], -$maxHistory);
    $pastQuestions = [];
}

$retrievalQuery = trim($question . ' ' . implode(' ', array_slice($pastQuestions, 0, 3)));
$contextBlocks = (new AiKnowledgeRepository())->search($retrievalQuery);
$answer = (new LlmService())->ask($question, $contextBlocks, $recentHistory);

$recommendText = $question . ' ' . implode(' ', array_slice($pastQuestions, 0, 5));
$recommendations = (new ArticleRecommendationRepository())->recommend($recommendText, 3);
if ($recommendations) {
    $answer .= "\n\n### مقاله‌های پیشنهادی برای شما";
    foreach ($recommendations as $article) {
        $url = 'article.php?slug=' . rawurlencode((string) $article['slug']);
        $answer .= "\n• {$article['title']} — {$url}";
    }
}

if ($userId) {
    $chatRepository->saveMessage($userId, 'user', $question);
    $chatRepository->saveMessage($userId, 'assistant', $answer);
}

$_SESSION['ai_chat_history'] = isset($_SESSION['ai_chat_history']) && is_array($_SESSION['ai_chat_history']) ? $_SESSION['ai_chat_history'] : [];
$_SESSION['ai_chat_history'][] = ['role' => 'user', 'content' => $question];
$_SESSION['ai_chat_history'][] = ['role' => 'assistant', 'content' => $answer];
if (count($_SESSION['ai_chat_history']) > 40) {
    $_SESSION['ai_chat_history'] = array_slice($_SESSION['ai_chat_history'], -40);
}

echo json_encode(['answer' => $answer], JSON_UNESCAPED_UNICODE);
