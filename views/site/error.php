<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var int $statusCode */

use yii\bootstrap5\Html;

$this->title = $name;
?>
<div class="error-block">
    <p class="error-code"><?= (int) $statusCode ?></p>
    <h1><?= Html::encode($name) ?></h1>
    <p><?= Html::encode($message) ?></p>
    <?= Html::a(
        Yii::$app->user->isGuest ? 'Back to sign in' : 'Back to dashboard',
        Yii::$app->user->isGuest ? ['/site/login'] : ['/site/index'],
        ['class' => 'btn btn-primary'],
    ) ?>
</div>
