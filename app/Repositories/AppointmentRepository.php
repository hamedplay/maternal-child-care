<?php

namespace App\Repositories;

use App\Core\Database;
use PDOException;

class AppointmentRepository
{
    public function create(int $userId, int $doctorId, string $scheduledAt, ?string $reason = null): bool
    {
        try {
            $reminderAt = date('Y-m-d H:i:s', strtotime($scheduledAt) - 86400);
            $stmt = Database::connection()->prepare(
                'INSERT INTO appointments (user_id, doctor_id, scheduled_at, reminder_at, reason)
                 VALUES (:user_id, :doctor_id, :scheduled_at, :reminder_at, :reason)'
            );
            return $stmt->execute([
                'user_id' => $userId,
                'doctor_id' => $doctorId,
                'scheduled_at' => $scheduledAt,
                'reminder_at' => $reminderAt,
                'reason' => $reason,
            ]);
        } catch (PDOException $e) {
            error_log('AppointmentRepository::create ' . $e->getMessage());
            return false;
        }
    }

    public function forUser(int $userId): array
    {
        try {
            $stmt = Database::connection()->prepare(
                'SELECT a.*, d.full_name AS doctor_name, d.specialty
                 FROM appointments a JOIN doctors d ON d.id = a.doctor_id
                 WHERE a.user_id = :user_id ORDER BY a.scheduled_at DESC'
            );
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function dueReminders(int $limit = 100): array
    {
        try {
            $limit = max(1, min(500, $limit));
            return Database::connection()->query(
                "SELECT a.id, a.scheduled_at, u.phone, d.full_name AS doctor_name
                 FROM appointments a
                 JOIN users u ON u.id = a.user_id
                 JOIN doctors d ON d.id = a.doctor_id
                 WHERE a.status = 'booked'
                   AND a.reminder_sent_at IS NULL
                   AND a.reminder_at IS NOT NULL
                   AND a.reminder_at <= NOW()
                   AND a.scheduled_at > NOW()
                 ORDER BY a.reminder_at ASC LIMIT {$limit}"
            )->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function markReminderSent(int $id): void
    {
        try {
            $stmt = Database::connection()->prepare('UPDATE appointments SET reminder_sent_at = NOW() WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
        }
    }
}
