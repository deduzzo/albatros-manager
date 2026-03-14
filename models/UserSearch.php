<?php

namespace app\models;

use yii\data\ActiveDataProvider;

/**
 * UserSearch - Ricerca e filtro utenti admin.
 */
class UserSearch extends User
{
    /**
     * @var string ricerca full-text
     */
    public $ricerca;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ricerca', 'ruolo'], 'safe'],
            [['status', 'totp_enabled'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ricerca' => 'Cerca',
        ]);
    }

    /**
     * Crea il DataProvider per la ricerca.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['nominativo' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['ruolo' => $this->ruolo]);
        $query->andFilterWhere(['totp_enabled' => $this->totp_enabled]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['or',
                ['like', 'username', $this->ricerca],
                ['like', 'nominativo', $this->ricerca],
                ['like', 'email', $this->ricerca],
            ]);
        }

        return $dataProvider;
    }
}
