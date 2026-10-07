<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\services\RbacService;
use yii\filters\AccessControl;
use yii\web\Controller;

class UserController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [RbacService::PERMISSION_VIEW_USERS],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        // with('leads') is the eager load for User::getLeads(). Three staff
        // rows then share one query for their leads instead of one query each.
        $users = User::find()
            ->with(['leads'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'users' => $users,
        ]);
    }
}
