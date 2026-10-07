<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\User;
use app\services\RbacService;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);

$identity = Yii::$app->user->identity;
$identity = $identity instanceof User ? $identity : null;
$controllerId = Yii::$app->controller->id ?? '';
$nav = [
    ['dashboard', 'site', Url::to(['/site/index']), 'bi-grid', 'Dashboard'],
    ['leads', 'lead', Url::to(['/lead/index']), 'bi-person-lines-fill', 'Leads'],
];
if (Yii::$app->user->can(RbacService::PERMISSION_VIEW_USERS)) {
    $nav[] = ['users', 'user', Url::to(['/user/index']), 'bi-people', 'Staff'];
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · Northline' : 'Northline') ?></title>
</head>
<body>
<?php $this->beginBody() ?>
<div class="app-shell">
    <aside class="app-sidebar d-none d-lg-flex">
        <a class="brand" href="<?= Url::to(['/site/index']) ?>">
            <span class="brand-mark">N</span>
            <span>
                <strong>Northline</strong>
                <small>Lead desk</small>
            </span>
        </a>
        <?= $this->render('_nav', ['nav' => $nav, 'controllerId' => $controllerId]) ?>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <button class="btn btn-menu d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title"><?= Html::encode($this->title ?: 'Northline') ?></div>
            <?php if ($identity !== null): ?>
                <div class="topbar-user">
                    <div class="user-meta">
                        <strong><?= Html::encode($identity->name) ?></strong>
                        <span><?= Html::encode($identity->getRoleLabel()) ?></span>
                    </div>
                    <?= Html::beginForm(['/site/logout'], 'post') ?>
                    <?= Html::submitButton('Log out', ['class' => 'btn btn-quiet']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif ?>
        </header>
        <main class="app-content">
            <?= \app\widgets\Alert::widget() ?>
            <?= $content ?>
        </main>
    </div>
</div>

<div class="offcanvas offcanvas-start app-offcanvas" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header">
        <span class="brand-mark">N</span>
        <strong>Northline</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?= $this->render('_nav', ['nav' => $nav, 'controllerId' => $controllerId]) ?>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
