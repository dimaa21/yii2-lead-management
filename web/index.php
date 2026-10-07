<?php

declare(strict_types=1);

require __DIR__ . '/../config/env.php';

defined('YII_DEBUG') or define('YII_DEBUG', filter_var(env('YII_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV') or define('YII_ENV', env('YII_ENV', 'dev') ?? 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
