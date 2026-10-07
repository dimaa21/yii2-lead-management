<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\User[] $users */

use app\components\UserRole;
use yii\bootstrap5\Html;

$this->title = 'Staff';
?>
<p class="page-lead">Everyone who can sign in. Managers work with leads. Admins can also delete leads and open this page.</p>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Leads</th>
                <th>Joined</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= Html::encode($user->name) ?></td>
                    <td><?= Html::encode($user->email) ?></td>
                    <td>
                        <span class="role-badge role-<?= Html::encode($user->role) ?>">
                            <?= Html::encode(UserRole::tryFrom($user->role)?->label() ?? $user->role) ?>
                        </span>
                    </td>
                    <td><?= count($user->leads) ?></td>
                    <td class="text-secondary"><?= Yii::$app->formatter->asDatetime($user->created_at) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</section>
