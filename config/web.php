<?php

use yii\helpers\ArrayHelper;

$common = require __DIR__ . '/common.php';

$cookieKey = env('COOKIE_VALIDATION_KEY', 'dev-northline-cookie-key-change-in-production');
$devCookieKeys = [
    'dev-northline-cookie-key-change-in-production',
    '51bQPhLuZ_Ok7kdyyaEH-F6M59aeomaM',
];

if (defined('YII_ENV_PROD') && YII_ENV_PROD && in_array($cookieKey, $devCookieKeys, true)) {
    throw new RuntimeException('Set COOKIE_VALIDATION_KEY before running in production.');
}

$config = ArrayHelper::merge($common, [
    'id' => 'northline',
    'homeUrl' => ['/site/index'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieKey,
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['/site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'dashboard' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                'users' => 'user/index',
                'leads' => 'lead/index',
                'leads/create' => 'lead/create',
                'leads/view/<id:\d+>' => 'lead/view',
                'leads/update/<id:\d+>' => 'lead/update',
                'leads/delete/<id:\d+>' => 'lead/delete',
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => ['leads' => 'api/lead'],
                    'prefix' => 'api',
                    'pluralize' => false,
                    'except' => ['options'],
                    'tokens' => [
                        '{id}' => '<id:\\d+>',
                    ],
                ],
            ],
        ],
    ],
]);

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
