<?php declare(strict_types=1); ?>
<div class="panel">
    <h2>Groups</h2>
    <?php if ($groups === []): ?>
        <p>No groups available.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td><?= (int)$group->id ?></td>
                    <td><?= ViewHelper::h($group->name) ?></td>
                    <td class="actions">
                        <a href="index.php?action=view&id=<?= (int)$group->id ?>">View</a>
                        <a href="index.php?action=edit&id=<?= (int)$group->id ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="index.php?action=create">Create New Group</a></p>
</div>

