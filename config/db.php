<?php

require_once __DIR__ . '/env.php';

return [
    'class' => \yii\db\Connection::class,
    'dsn' => env('DB_DSN', 'mysql:host=127.0.0.1;dbname=northline'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'enableSchemaCache' => env('YII_ENV', 'dev') === 'prod',
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
