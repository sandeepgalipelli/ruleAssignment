<?php
declare(strict_types=1);

class RuleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, type FROM rules ORDER BY id');
        $rows = $stmt->fetchAll();
        return array_map(static fn(array $row) => new Rule($row), $rows);
    }

    public function mapById(): array
    {
        $result = [];
        foreach ($this->all() as $rule) {
            $result[$rule->id] = $rule;
        }
        return $result;
    }
}

