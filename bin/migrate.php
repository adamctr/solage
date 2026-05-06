<?php
// CLI entry point for database migrations.
// Run via `docker compose run --rm migrate` or as part of `docker compose up`.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/autoload.php';
Autoloader::register();
require __DIR__ . '/../includes/database.php';

try {
    (new Migrations())->migrate();
    Logger::get()->info('migrate.completed');
    exit(0);
} catch (Throwable $e) {
    Logger::get()->critical('migrate.failed', ['exception' => $e]);
    exit(1);
}
