<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Lead $model */

use app\services\RbacService;
use yii\bootstrap5\Html;

$this->title = $model->name;
?>
<div class="page-head">
    <div>
        <?= $this->render('_status', ['status' => (string) $model->status]) ?>
        <h1 class="mt-2 mb-0"><?= Html::encode($model->name) ?></h1>
    </div>
    <div class="page-actions">
        <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if (Yii::$app->user->can(RbacService::PERMISSION_DELETE_LEADS)): ?>
            <?= Html::beginForm(['delete', 'id' => $model->id], 'post', [
                'onsubmit' => 'return confirm("Delete this lead?");',
            ]) ?>
            <?= Html::submitButton('Delete', ['class' => 'btn btn-outline-danger']) ?>
            <?= Html::endForm() ?>
        <?php endif ?>
    </div>
</div>

<section class="panel">
    <dl class="detail-grid">
        <div>
            <dt>Email</dt>
            <dd><?= Html::mailto($model->email) ?></dd>
        </div>
        <div>
            <dt>Phone</dt>
            <dd><?= Html::encode($model->phone ?: '—') ?></dd>
        </div>
        <div>
            <dt>Source</dt>
            <dd><?= Html::encode($model->getSourceLabel()) ?></dd>
        </div>
        <div>
            <dt>Status</dt>
            <dd><?= Html::encode($model->getStatusLabel()) ?></dd>
        </div>
        <div>
            <dt>Manager</dt>
            <dd><?= Html::encode($model->manager?->name ?? '—') ?></dd>
        </div>
        <div>
            <dt>Created</dt>
            <dd><?= Yii::$app->formatter->asDatetime($model->created_at) ?></dd>
        </div>
        <div>
            <dt>Updated</dt>
            <dd><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></dd>
        </div>
    </dl>
    <div class="comment-block">
        <h2>Comment</h2>
        <?php if ($model->comment === null || $model->comment === ''): ?>
            <p class="text-secondary mb-0">No comment.</p>
        <?php else: ?>
            <p class="mb-0"><?= nl2br(Html::encode($model->comment)) ?></p>
        <?php endif ?>
    </div>
</section>

<p class="mt-3 mb-0"><?= Html::a('Back to leads', ['index'], ['class' => 'btn btn-quiet']) ?></p>
