<?php

declare(strict_types=1);

/** @var string $status */

use app\components\LeadStatus;
use yii\bootstrap5\Html;

$enum = LeadStatus::tryFrom($status);
$label = $enum?->label() ?? $status;
$class = $enum?->badgeClass() ?? 'status-rejected';

echo Html::tag('span', Html::encode($label), ['class' => 'status-badge ' . $class]);
