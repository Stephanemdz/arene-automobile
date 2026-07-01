<?php
declare(strict_types=1);
class ContactModel
{
    public function __construct(private PDO $pdo) {}
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contact_messages (name, email, subject, message)
             VALUES (:name, :email, :subject, :message)'
        );

        $stmt->execute([
            ':name'    => $data['name'],
            ':email'   => $data['email'],
            ':subject' => $data['subject'],
            ':message' => $data['message'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
    public function getAll(bool $unreadOnly = false): array
    {
        $sql = 'SELECT * FROM contact_messages';
        if ($unreadOnly) {
            $sql .= ' WHERE is_read = 0';
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE contact_messages SET is_read = 1 WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM contact_messages WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function countUnread(): int
    {
        $stmt = $this->pdo->query(
            'SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'
        );
        return (int) $stmt->fetchColumn();
    }
}