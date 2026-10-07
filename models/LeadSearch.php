<?php

declare(strict_types=1);

namespace app\models;

use app\components\LeadSource;
use app\components\LeadStatus;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Filter, sort and paginate leads for the UI and the API.
 *
 * Every filter goes through ActiveQuery::andFilterWhere(), which binds the
 * value as a parameter. User input is never concatenated into SQL.
 */
class LeadSearch extends Model
{
    public const SORTABLE = [
        'id',
        'name',
        'email',
        'phone',
        'source',
        'status',
        'created_at',
        'updated_at',
    ];

    public ?string $name = null;
    public ?string $email = null;
    public ?string $status = null;
    public ?string $source = null;
    public ?string $manager_id = null;

    public function rules(): array
    {
        return [
            [['name', 'email', 'status', 'source', 'manager_id'], 'trim'],
            [['name', 'email'], 'string', 'max' => 255],
            ['status', 'in', 'range' => LeadStatus::values(), 'skipOnEmpty' => true],
            ['source', 'in', 'range' => LeadSource::values(), 'skipOnEmpty' => true],
            ['manager_id', 'integer', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'status' => 'Status',
            'source' => 'Source',
            'manager_id' => 'Manager',
        ];
    }

    public function search(array $params, int $pageSize = 10): ActiveDataProvider
    {
        $query = Lead::find()->with(['manager']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'pageSizeLimit' => [1, 100],
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC, 'id' => SORT_DESC],
                'attributes' => self::SORTABLE,
            ],
        ]);

        // The web form posts LeadSearch[name]. The API uses ?name= on the query string.
        if (!$this->load($params)) {
            $this->load($params, '');
        }

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['source' => $this->source])
            ->andFilterWhere(['manager_id' => $this->manager_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }

    public static function isSortValid(string $sort): bool
    {
        if ($sort === '') {
            return true;
        }

        foreach (explode(',', $sort) as $part) {
            $name = ltrim(trim($part), '-');
            if ($name === '' || !in_array($name, self::SORTABLE, true)) {
                return false;
            }
        }

        return true;
    }
}
