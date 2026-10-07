<?php

use yii\helpers\ArrayHelper;

$common = require __DIR__ . '/common.php';

$config = ArrayHelper::merge($common, [
    'id' => 'northline-console',
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@tests' => '@app/tests',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@yii/rbac/migrations',
                '@app/migrations',
            ],
            'migrationNamespaces' => [],
        ],
        'serve' => [
            'class' => \yii\console\controllers\ServeController::class,
            'docroot' => '@app/web',
            'router' => '@app/web/router.php',
        ],
    ],
]);

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
