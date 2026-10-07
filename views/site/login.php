<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Sign in';
?>
<h1>Sign in</h1>
<p class="text-secondary mb-4">Use your Northline account to open the lead desk.</p>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
<?= $form->field($model, 'email')->input('email', ['autofocus' => true, 'autocomplete' => 'username']) ?>
<?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password']) ?>
<?= $form->field($model, 'rememberMe')->checkbox() ?>
<div class="d-grid mt-2">
    <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary btn-lg', 'name' => 'login-button']) ?>
</div>
<?php ActiveForm::end(); ?>

<p class="auth-switch">No account yet? <?= Html::a('Create an account', ['/site/register']) ?></p>

<aside class="demo-box">
    <strong>Demo access</strong>
    <span>admin@northline.test · Admin12345</span>
    <span>mark@northline.test · Manager12345</span>
</aside>
