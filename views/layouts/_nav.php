<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array<int, array{0: string, 1: string, 2: string, 3: string, 4: string}> $nav */
/** @var string $controllerId */

use yii\bootstrap5\Html;

?>
<nav class="side-nav">
    <?php foreach ($nav as $item): ?>
        <?php [$key, $controller, $url, $icon, $label] = $item; ?>
        <?= Html::a(
            '<i class="bi ' . Html::encode($icon) . '"></i><span>' . Html::encode($label) . '</span>',
            $url,
            [
                'class' => 'side-link' . ($controllerId === $controller ? ' is-active' : ''),
                'encode' => false,
            ],
        ) ?>
    <?php endforeach ?>
</nav>
