<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class AiChatRepository
{
    public function recentMessages(int $userId, int $limit = 12): array
    {
        try {
            $limit = max(1, min(50, $limit));
            $stmt = Database::connection()->prepare(
                "SELECT role, content FROM ai_messages WHERE user_id = :user_id ORDER BY id DESC LIMIT {$limit}"
            );
            $stmt->execute(['user_id' => $userId]);
            return array_reverse($stmt->fetchAll());
        } catch (PDOException $e) {
            error_log('AiChatRepository::recentMessages: ' . $e->getMessage());
            return [];
        }
    }

    public function saveMessage(int $userId, string $role, string $content): void
    {
        if (!in_array($role, ['user', 'assistant'], true)) {
            return;
        }
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO ai_messages (user_id, role, content) VALUES (:user_id, :role, :content)'
            );
            $stmt->execute(['user_id' => $userId, 'role' => $role, 'content' => $content]);
        } catch (PDOException $e) {
            error_log('AiChatRepository::saveMessage: ' . $e->getMessage());
        }
    }

    public function recentUserQuestions(int $userId, int $limit = 8): array
    {
        try {
            $limit = max(1, min(30, $limit));
            $stmt = Database::connection()->prepare(
                "SELECT content FROM ai_messages WHERE user_id = :user_id AND role = 'user' ORDER BY id DESC LIMIT {$limit}"
            );
            $stmt->execute(['user_id' => $userId]);
            return array_column($stmt->fetchAll(), 'content');
        } catch (PDOException $e) {
            return [];
        }
    }
}
