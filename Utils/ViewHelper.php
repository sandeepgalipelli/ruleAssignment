<?php
declare(strict_types=1);

class ViewHelper
{
    public static function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function renderTree(array $nodes): void
    {
        if ($nodes === []) {
            return;
        }

        echo '<ul>';
        foreach ($nodes as $node) {
            /** @var Assignment $assignment */
            $assignment = $node['assignment'];
            echo '<li>';
            echo 'Tier ' . (int)$assignment->tier . ': ' . self::h($assignment->ruleName) . ' [' . strtoupper(self::h($assignment->ruleType)) . ']';
            self::renderTree($node['children']);
            echo '</li>';
        }
        echo '</ul>';
    }
}

