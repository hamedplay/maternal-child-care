<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class ChildRepository
{
    public function forUser(int $userId): array
    {
        try {
            $stmt = Database::connection()->prepare('SELECT * FROM children WHERE user_id = :user_id ORDER BY birth_date DESC, id DESC');
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function findOwned(int $id, int $userId): ?array
    {
        try {
            $stmt = Database::connection()->prepare('SELECT * FROM children WHERE id = :id AND user_id = :user_id LIMIT 1');
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) { return null; }
    }

    public function create(int $userId, array $data): bool
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO children (user_id, full_name, birth_date, gender, blood_type, notes)
                 VALUES (:user_id, :full_name, :birth_date, :gender, :blood_type, :notes)'
            );
            return $stmt->execute([
                'user_id' => $userId,
                'full_name' => $data['full_name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);
        } catch (PDOException $e) {
            error_log('ChildRepository::create ' . $e->getMessage());
            return false;
        }
    }
}
