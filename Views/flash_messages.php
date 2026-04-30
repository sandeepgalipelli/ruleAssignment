<?php declare(strict_types=1); ?>
<?php if (isset($_GET['saved'])): ?>
    <p class="success">Assignments saved successfully.</p>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <p class="error"><?= ViewHelper::h($error) ?></p>
<?php endforeach; ?>

