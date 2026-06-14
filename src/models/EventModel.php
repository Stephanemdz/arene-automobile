<?php
declare(strict_types=1);

class EventModel
{
    public function __construct(private PDO $pdo) {}


    /* Tous les événements acceptés */
    public function getAccepted(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, event_date, event_time, location,
                    latitude, longitude, type, cover_image
             FROM   events
             WHERE  status = :status
             ORDER  BY event_date ASC'
        );
        $stmt->execute([':status' => 'accepte']);
        return $stmt->fetchAll();
    }

    /* Événements filtrables pour le back-office admin. */
    public function getFiltered(?string $status, ?string $type): array
    {
        $sql    = 'SELECT e.*, u.username FROM events e
                   JOIN   users u ON u.id = e.user_id
                   WHERE  1=1';
        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= ' AND e.status = :status';
            $params[':status'] = $status;
        }
        if ($type !== null && $type !== '') {
            $sql .= ' AND e.type = :type';
            $params[':type'] = $type;
        }

        $sql .= ' ORDER BY e.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* Un seul événement par ID. */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, u.username
             FROM   events e
             JOIN   users  u ON u.id = e.user_id
             WHERE  e.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO events
                (user_id, title, description, event_date, event_time,
                 location, latitude, longitude, type, cover_image)
             VALUES
                (:user_id, :title, :description, :event_date, :event_time,
                 :location, :latitude, :longitude, :type, :cover_image)'
        );

        $stmt->execute([
            ':user_id'     => $data['user_id'],
            ':title'       => $data['title'],
            ':description' => $data['description'],
            ':event_date'  => $data['event_date'],
            ':event_time'  => $data['event_time']  ?? null,
            ':location'    => $data['location'],
            ':latitude'    => $data['latitude']    ?? null,
            ':longitude'   => $data['longitude']   ?? null,
            ':type'        => $data['type'],
            ':cover_image' => $data['cover_image'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }


    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['en_attente', 'accepte', 'refuse'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE events SET status = :status WHERE id = :id'
        );
        $stmt->execute([':status' => $status, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
