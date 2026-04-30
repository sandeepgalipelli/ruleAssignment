<?php
declare(strict_types=1);

class GroupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM groups ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        return array_map(static fn(array $row) => new Group($row), $rows);
    }

    public function find(int $id): ?Group
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM groups WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Group($row) : null;
    }

    public function create(string $name): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO groups(name) VALUES(:name)');
        $stmt->execute(['name' => $name]);
        return (int)$this->pdo->lastInsertId();
    }
}

