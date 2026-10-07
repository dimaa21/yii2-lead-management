<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LeadSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, string> $managers */

use app\components\LeadSource;
use app\components\LeadStatus;
use app\models\Lead;
use app\services\RbacService;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;

$this->title = 'Leads';
?>
<div class="page-head">
    <div>
        <p class="page-lead mb-0">Search by name or email, then narrow by status, source, or manager.</p>
    </div>
    <?= Html::a('New lead', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<section class="panel mb-3">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['index'],
        'options' => ['class' => 'filter-form'],
    ]); ?>
    <div class="row g-3 align-items-end">
        <div class="col-md-6 col-xl-3">
            <?= $form->field($searchModel, 'name')->textInput(['placeholder' => 'Anna']) ?>
        </div>
        <div class="col-md-6 col-xl-3">
            <?= $form->field($searchModel, 'email')->textInput(['placeholder' => 'anna@']) ?>
        </div>
        <div class="col-md-4 col-xl-2">
            <?= $form->field($searchModel, 'status')->dropDownList(LeadStatus::options(), ['prompt' => 'All']) ?>
        </div>
        <div class="col-md-4 col-xl-2">
            <?= $form->field($searchModel, 'source')->dropDownList(LeadSource::options(), ['prompt' => 'All']) ?>
        </div>
        <div class="col-md-4 col-xl-2">
            <?= $form->field($searchModel, 'manager_id')->dropDownList($managers, ['prompt' => 'All']) ?>
        </div>
    </div>
    <div class="form-actions">
        <?= Html::submitButton('Apply filters', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-quiet']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</section>

<section class="panel">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table align-middle mb-0'],
        'headerRowOptions' => ['class' => 'table-light'],
        'emptyText' => 'No leads match these filters.',
        'summary' => 'Showing {begin}–{end} of {totalCount}',
        'layout' => "<div class=\"table-responsive\">{items}</div>\n<div class=\"table-footer\">{summary}{pager}</div>",
        'pager' => [
            'class' => LinkPager::class,
            'options' => ['class' => 'pagination mb-0'],
        ],
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => static fn (Lead $model): string => Html::a(
                    Html::encode($model->name),
                    ['view', 'id' => $model->id],
                    ['class' => 'lead-name'],
                ),
            ],
            'email:email',
            'phone:text',
            [
                'attribute' => 'source',
                'value' => static fn (Lead $model): string => $model->getSourceLabel(),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => static fn (Lead $model): string => Yii::$app->view->render('/lead/_status', [
                    'status' => (string) $model->status,
                ]),
            ],
            [
                'label' => 'Manager',
                'value' => static fn (Lead $model): string => $model->manager?->name ?? '—',
            ],
            'created_at:datetime',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'contentOptions' => ['class' => 'text-nowrap'],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can(RbacService::PERMISSION_DELETE_LEADS),
                ],
                'buttons' => [
                    'delete' => static function (string $url, Lead $model): string {
                        return Html::a(
                            'Delete',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'action-delete',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Delete this lead?',
                                ],
                            ],
                        );
                    },
                ],
            ],
        ],
    ]) ?>
</section>
