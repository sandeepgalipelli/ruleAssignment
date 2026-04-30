<?php
declare(strict_types=1);

class Assignment
{
    public int $id;
    public int $groupId;
    public int $ruleId;
    public ?int $parentAssignmentId;
    public int $tier;
    public int $sortOrder;
    public string $ruleName;
    public string $ruleType;

    public function __construct(array $row)
    {
        $this->id = (int)$row['id'];
        $this->groupId = (int)$row['group_id'];
        $this->ruleId = (int)$row['rule_id'];
        $this->parentAssignmentId = $row['parent_assignment_id'] === null ? null : (int)$row['parent_assignment_id'];
        $this->tier = (int)$row['tier'];
        $this->sortOrder = (int)$row['sort_order'];
        $this->ruleName = (string)$row['rule_name'];
        $this->ruleType = (string)$row['rule_type'];
    }
}
