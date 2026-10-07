<?php

declare(strict_types=1);

namespace app\services;

use app\components\LeadStatus;
use app\components\UserRole;
use app\models\Lead;
use app\models\User;
use Yii;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Lead rules that should not live in a controller or a view:
 * who may assign a manager, who may delete, and the dashboard counts.
 */
final class LeadService
{
    public function scenarioFor(User $actor): string
    {
        return $actor->role === UserRole::Admin->value
            ? Lead::SCENARIO_ADMIN
            : Lead::SCENARIO_MANAGER;
    }

    /**
     * A new lead always has an owner. Managers are assigned to themselves.
     * Admins keep the manager they picked, or fall back to themselves.
     */
    public function assignOwner(Lead $lead, User $actor): void
    {
        if (!$lead->isNewRecord) {
            return;
        }

        if ($lead->scenario !== Lead::SCENARIO_ADMIN || empty($lead->manager_id)) {
            $lead->manager_id = $actor->getId();
        }
    }

    public function delete(Lead $lead, User $actor): void
    {
        $auth = Yii::$app->authManager;
        if ($auth === null || !$auth->checkAccess($actor->getId(), RbacService::PERMISSION_DELETE_LEADS)) {
            throw new ForbiddenHttpException('Only an admin can delete a lead.');
        }

        if ($lead->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the lead.');
        }
    }

    /**
     * One grouped query. The dashboard does not cache or hardcode these numbers.
     *
     * @return array{total: int, new: int, in_progress: int, converted: int, rejected: int}
     */
    public function statusSummary(): array
    {
        $summary = [
            'total' => 0,
            'new' => 0,
            'in_progress' => 0,
            'converted' => 0,
            'rejected' => 0,
        ];

        $rows = Lead::find()
            ->select(['status', 'total' => new Expression('COUNT(*)')])
            ->groupBy(['status'])
            ->asArray()
            ->all();

        foreach ($rows as $row) {
            $status = (string) $row['status'];
            $total = (int) $row['total'];
            if (isset($summary[$status]) && LeadStatus::tryFrom($status) !== null) {
                $summary[$status] = $total;
            }
            $summary['total'] += $total;
        }

        return $summary;
    }

    /**
     * @return list<Lead>
     */
    public function recent(int $limit = 8): array
    {
        return Lead::find()
            ->with(['manager'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function managerOptions(): array
    {
        $rows = User::find()
            ->select(['id', 'name'])
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();

        $options = [];
        foreach ($rows as $row) {
            $options[(int) $row['id']] = (string) $row['name'];
        }

        return $options;
    }
}
