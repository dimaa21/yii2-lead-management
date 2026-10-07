<?php

declare(strict_types=1);

$db = require __DIR__ . '/db.php';
$db['dsn'] = 'sqlite:' . dirname(__DIR__) . '/runtime/test.sqlite';
unset($db['username'], $db['password'], $db['charset']);

return $db;
