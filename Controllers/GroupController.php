<?php
declare(strict_types=1);

class GroupController
{
    public function __construct(
        private GroupRepository $groupRepository,
        private RuleRepository $ruleRepository,
        private AssignmentRepository $assignmentRepository,
        private AssignmentService $assignmentService
    ) {
    }

    public function index(): void
    {
        $errors = [];
        $action = $_GET['action'] ?? 'list';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePost();
            }
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }

        $groups = $this->groupRepository->all();
        $rules = $this->ruleRepository->all();

        require __DIR__ . '/../Views/layout_header.php';
        require __DIR__ . '/../Views/flash_messages.php';
        require __DIR__ . '/../Views/groups_list.php';

        if ($action === 'create' || $action === 'edit') {
            $isEdit = $action === 'edit';
            $group = null;
            $assignmentRows = [];
            if ($isEdit) {
                $group = $this->groupRepository->find((int)($_GET['id'] ?? 0));
                if ($group !== null) {
                    $assignmentRows = $this->assignmentRepository->byGroup($group->id);
                }
            }
            require __DIR__ . '/../Views/group_form.php';
        }

        if ($action === 'view') {
            $group = $this->groupRepository->find((int)($_GET['id'] ?? 0));
            $assignments = $group === null ? [] : $this->assignmentRepository->byGroup($group->id);
            $tree = TreeBuilder::build($assignments);
            require __DIR__ . '/../Views/group_view.php';
        }

        require __DIR__ . '/../Views/layout_footer.php';
    }

    private function handlePost(): void
    {
        $formAction = $_POST['form_action'] ?? '';
        if ($formAction === 'create_group') {
            $name = trim((string)($_POST['group_name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Group name is required.');
            }
            $rows = $_POST['assignments'] ?? [];
            if (!is_array($rows) || $rows === []) {
                throw new RuntimeException('At least one assignment row is required.');
            }
            $groupId = $this->groupRepository->create($name);
            $this->assignmentService->saveGroupAssignments($groupId, $rows);
            header('Location: index.php?action=view&id=' . $groupId . '&saved=1');
            exit;
        }

        if ($formAction === 'edit_group') {
            $groupId = (int)($_POST['group_id'] ?? 0);
            if ($groupId <= 0 || $this->groupRepository->find($groupId) === null) {
                throw new RuntimeException('Invalid group.');
            }
            $rows = $_POST['assignments'] ?? [];
            if (!is_array($rows) || $rows === []) {
                throw new RuntimeException('At least one assignment row is required.');
            }
            $this->assignmentService->saveGroupAssignments($groupId, $rows);
            header('Location: index.php?action=view&id=' . $groupId . '&saved=1');
            exit;
        }
    }
}

