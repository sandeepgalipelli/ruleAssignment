<?php
declare(strict_types=1);

class AssignmentService
{
    public function __construct(
        private RuleRepository $ruleRepository,
        private AssignmentRepository $assignmentRepository
    ) {
    }

    public function saveGroupAssignments(int $groupId, array $inputRows): array
    {
        $normalized = $this->normalizeRows($inputRows);
        $this->assignmentRepository->replaceForGroup($groupId, $normalized);
        return $normalized;
    }

    private function normalizeRows(array $inputRows): array
    {
        $rules = $this->ruleRepository->mapById();
        $nodes = [];
        $order = 0;

        foreach ($inputRows as $key => $row) {
            $ruleId = isset($row['rule_id']) ? (int)$row['rule_id'] : 0;
            $parent = $row['parent_key'] ?? '';
            $parent = $parent === '' || $parent === 'ROOT' ? null : (string)$parent;
            if (!isset($rules[$ruleId])) {
                throw new RuntimeException('Invalid rule selected.');
            }

            $tempKey = (string)$key;
            $nodes[$tempKey] = [
                'temp_key' => $tempKey,
                'rule_id' => $ruleId,
                'parent_key' => $parent,
                'rule_type' => $rules[$ruleId]->type,
                'order' => $order++,
            ];
        }

        foreach ($nodes as $node) {
            if ($node['parent_key'] !== null && !isset($nodes[$node['parent_key']])) {
                throw new RuntimeException('Parent node does not exist.');
            }
        }

        $childrenByParent = [];
        foreach ($nodes as $node) {
            $parentKey = $node['parent_key'] ?? 'ROOT';
            $childrenByParent[$parentKey][] = $node['temp_key'];
        }

        $tierCache = [];
        $visiting = [];
        $computeTier = function ($key) use (&$computeTier, &$tierCache, &$visiting, $nodes): int {
            $cacheKey = (string)$key;
            if (isset($tierCache[$cacheKey])) {
                return $tierCache[$cacheKey];
            }
            if (isset($visiting[$cacheKey])) {
                throw new RuntimeException('Cycle detected in hierarchy.');
            }
            $visiting[$cacheKey] = true;
            $parent = $nodes[$key]['parent_key'];
            $tier = $parent === null ? 1 : ($computeTier($parent) + 1);
            unset($visiting[$cacheKey]);
            $tierCache[$cacheKey] = $tier;
            return $tier;
        };

        foreach (array_keys($nodes) as $key) {
            $tier = $computeTier($key);
            if ($tier > 3) {
                throw new RuntimeException('Maximum 3 tiers allowed.');
            }
            $nodes[$key]['tier'] = $tier;
        }

        $duplicateCheck = [];
        foreach ($nodes as $node) {
            $parent = $node['parent_key'] ?? 'ROOT';
            $signature = $parent . '#' . $node['rule_id'];
            if (isset($duplicateCheck[$signature])) {
                throw new RuntimeException('The same rule cannot be reused under the same parent.');
            }
            $duplicateCheck[$signature] = true;
        }

        foreach ($nodes as $node) {
            $childrenCount = count($childrenByParent[$node['temp_key']] ?? []);
            if ($node['rule_type'] === 'decision' && $childrenCount > 0) {
                throw new RuntimeException('Decision rule cannot have child rules.');
            }
            if ($node['rule_type'] === 'condition' && $childrenCount === 0) {
                throw new RuntimeException('Condition rule must have at least one child rule.');
            }
        }

        usort($nodes, static function (array $a, array $b): int {
            if ($a['tier'] === $b['tier']) {
                return $a['order'] <=> $b['order'];
            }
            return $a['tier'] <=> $b['tier'];
        });

        $sortByParent = [];
        foreach ($nodes as &$node) {
            $parent = $node['parent_key'] ?? 'ROOT';
            $sortByParent[$parent] = ($sortByParent[$parent] ?? 0) + 1;
            $node['sort_order'] = $sortByParent[$parent];
        }
        unset($node);

        return $nodes;
    }
}

