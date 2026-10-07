<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Lead $model */
/** @var array<int, string> $managers */

$this->title = 'Edit lead';
?>
<section class="panel form-panel">
    <h1>Edit <?= \yii\bootstrap5\Html::encode($model->name) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'managers' => $managers]) ?>
</section>
