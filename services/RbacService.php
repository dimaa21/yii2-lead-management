<?php

declare(strict_types=1);

namespace app\services;

use app\models\User;
use Yii;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Maps the role column on user to Yii's DbManager.
 *
 * AccessControl and User::can() ask the auth manager. The column is what we
 * show in the UI and what we assign. Both have to move together.
 */
final class RbacService
{
    public const PERMISSION_MANAGE_LEADS = 'manageLeads';
    public const PERMISSION_DELETE_LEADS = 'deleteLeads';
    public const PERMISSION_VIEW_USERS = 'viewUsers';

    public function ensureRoles(): void
    {
        $auth = $this->manager();

        $manageLeads = $this->permission($auth, self::PERMISSION_MANAGE_LEADS, 'View, create and update leads');
        $deleteLeads = $this->permission($auth, self::PERMISSION_DELETE_LEADS, 'Delete leads');
        $viewUsers = $this->permission($auth, self::PERMISSION_VIEW_USERS, 'View the staff directory');

        $manager = $this->role($auth, 'manager', 'Works with leads');
        $admin = $this->role($auth, 'admin', 'Full access to leads and staff');

        $this->link($auth, $manager, $manageLeads);
        // Admin inherits every manager permission, then gets delete and staff access on top.
        $this->link($auth, $admin, $manager);
        $this->link($auth, $admin, $deleteLeads);
        $this->link($auth, $admin, $viewUsers);
    }

    public function syncAssignment(User $user): void
    {
        $auth = $this->manager();
        $role = $auth->getRole($user->role);
        if ($role === null) {
            $this->ensureRoles();
            $role = $auth->getRole($user->role);
        }
        if ($role === null) {
            throw new \RuntimeException('Unknown role: ' . $user->role);
        }

        $auth->revokeAll($user->id);
        $auth->assign($role, $user->id);
    }

    private function manager(): DbManager
    {
        $auth = Yii::$app->authManager;
        if (!$auth instanceof DbManager) {
            throw new \RuntimeException('Configure authManager as yii\rbac\DbManager before using RBAC.');
        }

        return $auth;
    }

    private function permission(DbManager $auth, string $name, string $description): Permission
    {
        $permission = $auth->getPermission($name);
        if ($permission !== null) {
            return $permission;
        }

        $permission = $auth->createPermission($name);
        $permission->description = $description;
        $auth->add($permission);

        return $permission;
    }

    private function role(DbManager $auth, string $name, string $description): Role
    {
        $role = $auth->getRole($name);
        if ($role !== null) {
            return $role;
        }

        $role = $auth->createRole($name);
        $role->description = $description;
        $auth->add($role);

        return $role;
    }

    private function link(DbManager $auth, Item $parent, Item $child): void
    {
        if ($auth->hasChild($parent, $child)) {
            return;
        }

        $auth->addChild($parent, $child);
    }
}
