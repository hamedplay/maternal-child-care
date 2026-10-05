<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class ArticleRecommendationRepository
{
    private const STOPWORDS = ['برای','این','اون','است','هست','درباره','چطور','چگونه','آیا','من','شما','کودک','مادر','بارداری','چیست','چیه','که','در','به','از','با','و','یا','را'];

    public function recommend(string $text, int $limit = 3): array
    {
        $keywords = $this->keywords($text);
        if (!$keywords) {
            return [];
        }

        try {
            $conditions = [];
            $params = [];
            foreach (array_slice($keywords, 0, 6) as $i => $keyword) {
                $conditions[] = "(title LIKE :t{$i} OR content LIKE :c{$i})";
                $params["t{$i}"] = '%' . $keyword . '%';
                $params["c{$i}"] = '%' . $keyword . '%';
            }
            $limit = max(1, min(6, $limit));
            $sql = 'SELECT id, title, slug FROM articles WHERE is_active = 1 AND (' . implode(' OR ', $conditions) . ") ORDER BY updated_at DESC LIMIT {$limit}";
            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    private function keywords(string $text): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/u', trim((string) $clean));
        $result = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) < 3 || in_array($word, self::STOPWORDS, true)) {
                continue;
            }
            $result[] = $word;
        }
        return array_values(array_unique($result));
    }
}
