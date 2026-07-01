<?php
declare(strict_types=1);

class FavoriteModel
{
    public function __construct(private PDO $pdo) {}
    public function isFavorite(int $userId, int $eventId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM favorites
             WHERE user_id = :user_id AND event_id = :event_id'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':event_id' => $eventId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, f.created_at AS favorited_at
             FROM   favorites f
             JOIN   events e ON e.id = f.event_id
             WHERE  f.user_id = :user_id
             AND    e.status  = "accepte"
             ORDER  BY f.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countByEvent(int $eventId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM favorites WHERE event_id = :event_id'
        );
        $stmt->execute([':event_id' => $eventId]);
        return (int) $stmt->fetchColumn();
    }


    public function add(int $userId, int $eventId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO favorites (user_id, event_id)
                 VALUES (:user_id, :event_id)'
            );
            $stmt->execute([
                ':user_id'  => $userId,
                ':event_id' => $eventId,
            ]);
            return true;
        } catch (PDOException $e) {
            // Doublon : la contrainte UNIQUE KEY a bloqué l'insertion
            return false;
        }
    }

    public function remove(int $userId, int $eventId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM favorites
             WHERE user_id = :user_id AND event_id = :event_id'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':event_id' => $eventId,
        ]);
        return $stmt->rowCount() > 0;
    }
}