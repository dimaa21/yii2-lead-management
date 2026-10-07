<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array{total: int, new: int, in_progress: int, converted: int, rejected: int} $summary */
/** @var app\models\Lead[] $recent */

use yii\bootstrap5\Html;

$this->title = 'Dashboard';

$cards = [
    ['Total leads', $summary['total'], 'bi-collection', 'stat-total'],
    ['New', $summary['new'], 'bi-stars', 'stat-new'],
    ['In progress', $summary['in_progress'], 'bi-arrow-repeat', 'stat-progress'],
    ['Converted', $summary['converted'], 'bi-check2-circle', 'stat-converted'],
    ['Rejected', $summary['rejected'], 'bi-x-circle', 'stat-rejected'],
];
?>
<p class="page-lead">Counts are read from the leads table. They update as soon as a lead changes status.</p>

<section class="stat-grid">
    <?php foreach ($cards as [$label, $value, $icon, $tone]): ?>
        <article class="stat-card <?= Html::encode($tone) ?>">
            <div class="stat-icon"><i class="bi <?= Html::encode($icon) ?>"></i></div>
            <div>
                <div class="stat-value"><?= (int) $value ?></div>
                <div class="stat-label"><?= Html::encode($label) ?></div>
            </div>
        </article>
    <?php endforeach ?>
</section>

<section class="panel mt-4">
    <div class="panel-head">
        <h2>Recent leads</h2>
        <?= Html::a('View all', ['/lead/index'], ['class' => 'btn btn-quiet']) ?>
    </div>
    <?php if ($recent === []): ?>
        <p class="empty-copy">No leads yet. Create the first one from the leads page.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Source</th>
                    <th>Manager</th>
                    <th>Created</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $lead): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($lead->name), ['/lead/view', 'id' => $lead->id], ['class' => 'lead-name']) ?></td>
                        <td><?= $this->render('/lead/_status', ['status' => (string) $lead->status]) ?></td>
                        <td><?= Html::encode($lead->getSourceLabel()) ?></td>
                        <td><?= Html::encode($lead->manager?->name ?? '—') ?></td>
                        <td class="text-secondary"><?= Yii::$app->formatter->asDatetime($lead->created_at) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
