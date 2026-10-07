<?php

declare(strict_types=1);

namespace app\models;

use app\components\UserRole;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Staff account.
 *
 * Authentication is session-based. The remember-me cookie does not need an
 * auth_key column: {@see getAuthKey()} is derived from the password hash,
 * so changing the password invalidates other sessions.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property int $created_at
 * @property int $updated_at
 * @property-read Lead[] $leads
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'email', 'password_hash', 'role'], 'required'],
            [['name', 'email'], 'trim'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['name', 'string', 'min' => 2, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'unique'],
            ['password_hash', 'string', 'max' => 255],
            ['role', 'in', 'range' => UserRole::values()],
        ];
    }

    /**
     * password_hash and role are validated, but the "!" prefix keeps them out
     * of mass assignment. Callers set them in code.
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['name', 'email', '!password_hash', '!role'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ];
    }

    /**
     * Never expose the password hash through toArray() or the REST serializer.
     *
     * @return array<int|string, string>
     */
    public function fields(): array
    {
        return ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
    }

    public function extraFields(): array
    {
        return [];
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword(string $password): bool
    {
        if ($this->password_hash === '') {
            return false;
        }

        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getRoleLabel(): string
    {
        return UserRole::tryFrom($this->role)?->label() ?? $this->role;
    }

    /**
     * Leads this person is responsible for.
     *
     * hasMany is the inverse of {@see Lead::getManager()}: one user can own
     * many leads. The staff page eager-loads this relation to show a workload
     * count without a query per row.
     */
    public function getLeads(): ActiveQuery
    {
        return $this->hasMany(Lead::class, ['manager_id' => 'id']);
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * The API uses the same session as the web UI, so access tokens are unused.
     */
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public static function findByEmail(string $email): ?self
    {
        $user = static::findOne(['email' => strtolower(trim($email))]);

        return $user instanceof self ? $user : null;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getAuthKey(): string
    {
        return hash_hmac(
            'sha256',
            (string) $this->getId(),
            (string) Yii::$app->params['authKeySalt'] . $this->password_hash,
        );
    }

    public function validateAuthKey($authKey): bool
    {
        return hash_equals($this->getAuthKey(), (string) $authKey);
    }
}
