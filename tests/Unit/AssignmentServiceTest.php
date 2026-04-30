<?php

declare(strict_types=1);

final class FakeRuleRepository extends RuleRepository
{
    public function __construct(private array $rulesMap)
    {
    }

    public function mapById(): array
    {
        return $this->rulesMap;
    }
}

final class FakeAssignmentRepository extends AssignmentRepository
{
    public array $savedRows = [];
    public int $savedGroupId = 0;

    public function __construct()
    {
    }

    public function replaceForGroup(int $groupId, array $rows): void
    {
        $this->savedGroupId = $groupId;
        $this->savedRows = $rows;
    }
}

function makeRule(int $id, string $name, string $type): Rule
{
    return new Rule(['id' => $id, 'name' => $name, 'type' => $type]);
}

function makeService(FakeAssignmentRepository $assignmentRepo): AssignmentService
{
    $rules = [
        1 => makeRule(1, 'Decision 1', 'decision'),
        2 => makeRule(2, 'Condition 1', 'condition'),
        3 => makeRule(3, 'Decision 2', 'decision'),
        4 => makeRule(4, 'Condition 2', 'condition'),
    ];

    $ruleRepo = new FakeRuleRepository($rules);
    return new AssignmentService($ruleRepo, $assignmentRepo);
}

it('saves valid hierarchy', function (): void {
    $assignmentRepo = new FakeAssignmentRepository();
    $service = makeService($assignmentRepo);

    $rows = [
        'a' => ['rule_id' => 2, 'parent_key' => ''],
        'b' => ['rule_id' => 1, 'parent_key' => 'a'],
    ];

    $normalized = $service->saveGroupAssignments(10, $rows);

    expect($assignmentRepo->savedGroupId)->toBe(10);
    expect($normalized)->toHaveCount(2);
    expect($normalized[0]['tier'])->toBe(1);
    expect($normalized[1]['tier'])->toBe(2);
});

it('fails when hierarchy exceeds 3 tiers', function (): void {
    $assignmentRepo = new FakeAssignmentRepository();
    $service = makeService($assignmentRepo);

    $rows = [
        'a' => ['rule_id' => 2, 'parent_key' => ''],
        'b' => ['rule_id' => 4, 'parent_key' => 'a'],
        'c' => ['rule_id' => 4, 'parent_key' => 'b'],
        'd' => ['rule_id' => 1, 'parent_key' => 'c'],
    ];

    $call = fn() => $service->saveGroupAssignments(10, $rows);
    expect($call)->toThrow(RuntimeException::class, 'Maximum 3 tiers allowed.');
});

it('fails when decision has children', function (): void {
    $assignmentRepo = new FakeAssignmentRepository();
    $service = makeService($assignmentRepo);

    $rows = [
        'a' => ['rule_id' => 1, 'parent_key' => ''],
        'b' => ['rule_id' => 3, 'parent_key' => 'a'],
    ];

    $call = fn() => $service->saveGroupAssignments(11, $rows);
    expect($call)->toThrow(RuntimeException::class, 'Decision rule cannot have child rules.');
});

it('fails when condition has no child', function (): void {
    $assignmentRepo = new FakeAssignmentRepository();
    $service = makeService($assignmentRepo);

    $rows = [
        'a' => ['rule_id' => 2, 'parent_key' => ''],
    ];

    $call = fn() => $service->saveGroupAssignments(12, $rows);
    expect($call)->toThrow(RuntimeException::class, 'Condition rule must have at least one child rule.');
});

it('fails when same rule repeats under same parent', function (): void {
    $assignmentRepo = new FakeAssignmentRepository();
    $service = makeService($assignmentRepo);

    $rows = [
        'a' => ['rule_id' => 1, 'parent_key' => ''],
        'b' => ['rule_id' => 1, 'parent_key' => ''],
    ];

    $call = fn() => $service->saveGroupAssignments(13, $rows);
    expect($call)->toThrow(RuntimeException::class, 'The same rule cannot be reused under the same parent.');
});

