<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

spl_autoload_register(static function (string $class): void {
    foreach (['Controllers', 'Models', 'Repositories', 'Services', 'Utils'] as $directory) {
        $path = __DIR__ . '/' . $directory . '/' . $class . '.php';
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

