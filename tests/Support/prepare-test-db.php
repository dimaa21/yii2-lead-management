<?php

declare(strict_types=1);

/**
 * Builds the sqlite database used by unit and functional tests.
 * The file is kept between runs. A missing lead table means migrate once.
 */

$root = dirname(__DIR__, 2);
$sqlite = $root . '/runtime/test.sqlite';

$ready = false;
if (is_file($sqlite)) {
    $pdo = new PDO('sqlite:' . $sqlite);
    $ready = $pdo->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'lead'")->fetchColumn() !== false;
    unset($pdo);
}

if ($ready) {
    return;
}

if (Yii::$app !== null) {
    throw new RuntimeException('Cannot migrate the test database because an application is already running.');
}

$config = require $root . '/config/console.php';
$config['components']['db'] = require $root . '/config/test_db.php';
$config['controllerMap']['migrate']['interactive'] = false;

$application = new yii\console\Application($config);
$exitCode = $application->runAction('migrate/up');
if ($exitCode !== 0) {
    throw new RuntimeException('Could not migrate the test database.');
}

unset($application);
Yii::$app = null;
