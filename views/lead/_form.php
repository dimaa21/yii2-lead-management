<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Lead $model */
/** @var array<int, string> $managers */

use app\components\LeadSource;
use app\components\LeadStatus;
use app\models\Lead;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<?php $form = ActiveForm::begin(['id' => 'lead-form']); ?>
<div class="row g-3">
    <div class="col-md-6">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => '+1 555 010 1000']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'source')->dropDownList(LeadSource::options(), ['prompt' => 'Select a source']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'status')->dropDownList(LeadStatus::options()) ?>
    </div>
    <div class="col-md-6">
        <?php if ($model->scenario === Lead::SCENARIO_ADMIN): ?>
            <?= $form->field($model, 'manager_id')->dropDownList($managers, ['prompt' => 'Select a manager']) ?>
        <?php else: ?>
            <div class="mb-3">
                <div class="form-label">Manager</div>
                <div class="form-control-plaintext">
                    <?= Html::encode($model->manager?->name ?? Yii::$app->user->identity?->name ?? 'You') ?>
                </div>
                <?php if ($model->isNewRecord): ?>
                    <div class="form-text">This lead will be assigned to you.</div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'comment')->textarea(['rows' => 5]) ?>
    </div>
</div>
<div class="form-actions">
    <?= Html::submitButton($model->isNewRecord ? 'Create lead' : 'Save changes', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Cancel', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-quiet']) ?>
</div>
<?php ActiveForm::end(); ?>
