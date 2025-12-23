<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
	fwrite(STDERR, "Missing autoload file: {$autoload}\nRun: cd apps/weather_apis && composer install\n");
	exit(1);
}

require_once $autoload;
