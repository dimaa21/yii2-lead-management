<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Create account';
?>
<h1>Create account</h1>
<p class="text-secondary mb-4">New accounts join as managers. An admin can be issued from the seed data.</p>

<?php $form = ActiveForm::begin(['id' => 'register-form']); ?>
<?= $form->field($model, 'name')->textInput(['autofocus' => true, 'autocomplete' => 'name']) ?>
<?= $form->field($model, 'email')->input('email', ['autocomplete' => 'username']) ?>
<?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password']) ?>
<?= $form->field($model, 'password_repeat')->passwordInput(['autocomplete' => 'new-password']) ?>
<div class="d-grid mt-2">
    <?= Html::submitButton('Create account', ['class' => 'btn btn-primary btn-lg']) ?>
</div>
<?php ActiveForm::end(); ?>

<p class="auth-switch">Already registered? <?= Html::a('Sign in', ['/site/login']) ?></p>
