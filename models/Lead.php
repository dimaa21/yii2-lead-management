<?php

declare(strict_types=1);

namespace app\models;

use app\components\LeadSource;
use app\components\LeadStatus;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * An inbound sales lead.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $source
 * @property string $status
 * @property string|null $comment
 * @property int $manager_id
 * @property int $created_at
 * @property int $updated_at
 * @property-read User|null $manager
 */
class Lead extends ActiveRecord
{
    public const SCENARIO_ADMIN = 'admin';
    public const SCENARIO_MANAGER = 'manager';

    public static function tableName(): string
    {
        return '{{%lead}}';
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
            [['name', 'email', 'source', 'status', 'manager_id'], 'required'],
            [['name', 'email', 'phone', 'comment'], 'trim'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['name', 'string', 'min' => 2, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['phone', 'string', 'max' => 32],
            ['phone', 'match', 'pattern' => '/^\+?[0-9\s().-]{6,32}$/', 'skipOnEmpty' => true],
            ['comment', 'string', 'max' => 5000],
            ['source', 'in', 'range' => LeadSource::values()],
            ['status', 'in', 'range' => LeadStatus::values()],
            ['manager_id', 'integer'],
            [
                'manager_id',
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'id',
                'message' => 'Selected manager does not exist.',
            ],
        ];
    }

    /**
     * Managers cannot reassign a lead. manager_id stays in the scenario with
     * a "!" prefix so it is still validated, but load() ignores a posted value.
     * Admins can set manager_id from the form or the API.
     */
    public function scenarios(): array
    {
        $shared = ['name', 'email', 'phone', 'source', 'status', 'comment'];

        return [
            self::SCENARIO_DEFAULT => array_merge($shared, ['!manager_id']),
            self::SCENARIO_MANAGER => array_merge($shared, ['!manager_id']),
            self::SCENARIO_ADMIN => array_merge($shared, ['manager_id']),
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'source' => 'Source',
            'status' => 'Status',
            'comment' => 'Comment',
            'manager_id' => 'Manager',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->phone === '') {
            $this->phone = null;
        }
        if ($this->comment === '') {
            $this->comment = null;
        }
        if ($this->isNewRecord && ($this->getAttribute('status') === null || $this->getAttribute('status') === '')) {
            $this->setAttribute('status', LeadStatus::New->value);
        }

        return parent::beforeValidate();
    }

    /**
     * The user responsible for this lead.
     *
     * hasOne is used because manager_id points at a single user. Views and the
     * API read $lead->manager instead of looking the user up by hand, and list
     * queries call with('manager') so the page does not run one query per row.
     */
    public function getManager(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    public function getStatusLabel(): string
    {
        return LeadStatus::tryFrom((string) $this->status)?->label() ?? (string) $this->status;
    }

    public function getSourceLabel(): string
    {
        return LeadSource::tryFrom((string) $this->source)?->label() ?? (string) $this->source;
    }

    /**
     * JSON shape for the leads API. The password hash never appears here.
     *
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'id' => static fn (self $model): int => (int) $model->id,
            'name' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'source' => 'source',
            'status' => 'status',
            'comment' => 'comment',
            'manager_id' => static fn (self $model): int => (int) $model->manager_id,
            'manager' => static function (self $model): ?array {
                if (empty($model->manager_id) || $model->manager === null) {
                    return null;
                }

                return [
                    'id' => (int) $model->manager->id,
                    'name' => $model->manager->name,
                ];
            },
            'created_at' => static fn (self $model): ?string => $model->created_at
                ? date(DATE_ATOM, (int) $model->created_at)
                : null,
            'updated_at' => static fn (self $model): ?string => $model->updated_at
                ? date(DATE_ATOM, (int) $model->updated_at)
                : null,
        ];
    }
}
