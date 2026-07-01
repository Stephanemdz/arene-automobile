<?php
declare(strict_types=1);

class CommentModel
{
    public function __construct(private PDO $pdo) {}

    public function getByEvent(int $eventId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, u.username
             FROM   comments c
             JOIN   users u ON u.id = c.user_id
             WHERE  c.event_id = :event_id
             ORDER  BY c.created_at DESC'
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function countByEvent(int $eventId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM comments WHERE event_id = :event_id'
        );
        $stmt->execute([':event_id' => $eventId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(int $userId, int $eventId, string $content): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (user_id, event_id, content)
             VALUES (:user_id, :event_id, :content)'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':event_id' => $eventId,
            ':content'  => $content,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare(
                'DELETE FROM comments WHERE id = :id'
            );
            $stmt->execute([':id' => $id]);
        } else {
            $stmt = $this->pdo->prepare(
                'DELETE FROM comments WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([':id' => $id, ':user_id' => $userId]);
        }
        return $stmt->rowCount() > 0;
    }
}