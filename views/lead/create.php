<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Lead $model */
/** @var array<int, string> $managers */

$this->title = 'New lead';
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
?>
<section class="panel form-panel">
    <h1>New lead</h1>
    <?= $this->render('_form', ['model' => $model, 'managers' => $managers]) ?>
</section>
