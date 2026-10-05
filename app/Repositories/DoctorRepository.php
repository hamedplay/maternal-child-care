<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class DoctorRepository
{
    public function allActive(): array
    {
        try {
            return Database::connection()->query(
                'SELECT id, full_name, specialty, medical_code, bio FROM doctors WHERE is_active = 1 ORDER BY full_name'
            )->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = Database::connection()->prepare('SELECT * FROM doctors WHERE id = :id AND is_active = 1 LIMIT 1');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
