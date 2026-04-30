<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pdo = Database::connection();
$groupRepository = new GroupRepository($pdo);
$ruleRepository = new RuleRepository($pdo);
$assignmentRepository = new AssignmentRepository($pdo);

$controller = new GroupController(
    $groupRepository,
    $ruleRepository,
    $assignmentRepository,
    new AssignmentService($ruleRepository, $assignmentRepository)
);

$controller->index();