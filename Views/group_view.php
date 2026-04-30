<?php declare(strict_types=1); ?>
<div class="panel">
    <h2>View Group</h2>
    <?php if ($group === null): ?>
        <p class="error">Group not found.</p>
    <?php else: ?>
        <p><strong>Group:</strong> <?= ViewHelper::h($group->name) ?></p>
        <?php if ($assignments === []): ?>
            <p>No assignments found.</p>
        <?php else: ?>
            <?php ViewHelper::renderTree($tree); ?>
        <?php endif; ?>
        <p><a href="index.php?action=edit&id=<?= (int)$group->id ?>">Edit Assignments</a></p>
    <?php endif; ?>
</div>

