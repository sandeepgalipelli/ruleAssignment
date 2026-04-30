<?php
declare(strict_types=1);

class TreeBuilder
{
    public static function build(array $assignments): array
    {
        $byParent = [];
        foreach ($assignments as $assignment) {
            $parent = $assignment->parentAssignmentId ?? 0;
            $byParent[$parent][] = $assignment;
        }
        return self::walk($byParent, 0);
    }

    private static function walk(array $byParent, int $parentId): array
    {
        $result = [];
        foreach ($byParent[$parentId] ?? [] as $assignment) {
            $result[] = [
                'assignment' => $assignment,
                'children' => self::walk($byParent, $assignment->id),
            ];
        }
        return $result;
    }
}

