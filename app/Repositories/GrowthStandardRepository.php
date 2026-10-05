<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class GrowthStandardRepository
{
    public function findByGenderAndAge(string $gender, int $ageMonths): ?array
    {
        if (!in_array($gender, ['boy', 'girl'], true)) return null;
        try {
            $stmt = Database::connection()->prepare(
                'SELECT gender, age_in_months, length_p3, length_p50, length_p97,
                        weight_p3, weight_p50, weight_p97, head_p3, head_p50, head_p97
                 FROM growth_standards WHERE gender = :gender AND age_in_months = :age LIMIT 1'
            );
            $stmt->execute(['gender' => $gender, 'age' => $ageMonths]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('GrowthStandardRepository::findByGenderAndAge خطا: ' . $e->getMessage());
            return null;
        }
    }

    public function maxAgeInMonths(): int
    {
        try {
            $max = Database::connection()->query('SELECT MAX(age_in_months) FROM growth_standards')->fetchColumn();
            return ($max !== false && $max !== null) ? (int) $max : 12;
        } catch (PDOException $e) { return 12; }
    }

    public function all(?string $gender = null): array
    {
        try {
            if (in_array($gender, ['boy', 'girl'], true)) {
                $stmt = Database::connection()->prepare('SELECT * FROM growth_standards WHERE gender = :gender ORDER BY age_in_months');
                $stmt->execute(['gender' => $gender]);
                return $stmt->fetchAll();
            }
            return Database::connection()->query('SELECT * FROM growth_standards ORDER BY gender, age_in_months')->fetchAll();
        } catch (PDOException $e) { return []; }
    }
}
