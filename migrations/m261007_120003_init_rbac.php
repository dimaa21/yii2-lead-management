<?php

declare(strict_types=1);

use app\services\RbacService;
use yii\db\Migration;

/**
 * Creates the admin and manager roles.
 *
 * Runs after the framework RBAC tables (auth_item, auth_assignment, ...),
 * which are included from @yii/rbac/migrations.
 */
class m261007_120003_init_rbac extends Migration
{
    public function safeUp(): void
    {
        (new RbacService())->ensureRoles();
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        if ($auth !== null) {
            $auth->removeAll();
        }
    }
}
