<?php
declare(strict_types=1);

class AssignmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function byGroup(int $groupId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.group_id, a.rule_id, a.parent_assignment_id, a.tier, a.sort_order, r.name AS rule_name, r.type AS rule_type
             FROM assignments a
             INNER JOIN rules r ON r.id = a.rule_id
             WHERE a.group_id = :group_id
             ORDER BY a.tier, a.sort_order, a.id'
        );
        $stmt->execute(['group_id' => $groupId]);
        $rows = $stmt->fetchAll();
        return array_map(static fn(array $row) => new Assignment($row), $rows);
    }

    public function replaceForGroup(int $groupId, array $rows): void
    {
        $this->pdo->beginTransaction();
        try {
            $deleteStmt = $this->pdo->prepare('DELETE FROM assignments WHERE group_id = :group_id');
            $deleteStmt->execute(['group_id' => $groupId]);

            $insertStmt = $this->pdo->prepare(
                'INSERT INTO assignments(group_id, rule_id, parent_assignment_id, tier, sort_order)
                 VALUES(:group_id, :rule_id, :parent_assignment_id, :tier, :sort_order)'
            );

            $idMap = [];
            foreach ($rows as $row) {
                $parentDbId = $row['parent_key'] === null ? null : ($idMap[$row['parent_key']] ?? null);
                $insertStmt->execute([
                    'group_id' => $groupId,
                    'rule_id' => $row['rule_id'],
                    'parent_assignment_id' => $parentDbId,
                    'tier' => $row['tier'],
                    'sort_order' => $row['sort_order'],
                ]);
                $idMap[$row['temp_key']] = (int)$this->pdo->lastInsertId();
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}

