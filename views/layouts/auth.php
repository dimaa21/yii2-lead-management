<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · Northline' : 'Northline') ?></title>
</head>
<body class="auth-body">
<?php $this->beginBody() ?>
<div class="auth-shell">
    <section class="auth-brand">
        <div class="brand-mark">N</div>
        <h1>Northline</h1>
        <p>The internal desk for inbound leads. Track who came in, who owns the conversation, and what happened next.</p>
    </section>
    <section class="auth-panel">
        <?= \app\widgets\Alert::widget() ?>
        <?= $content ?>
    </section>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
