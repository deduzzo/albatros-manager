<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ContoCorrenteSearch - Model di ricerca per ContoCorrente
 */
class ContoCorrenteSearch extends ContoCorrente
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['banca', 'iban', 'intestatario', 'note'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = ContoCorrente::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['banca' => SORT_ASC],
                'attributes' => [
                    'id',
                    'banca',
                    'iban',
                    'intestatario',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'banca', $this->banca])
            ->andFilterWhere(['like', 'iban', $this->iban])
            ->andFilterWhere(['like', 'intestatario', $this->intestatario]);

        return $dataProvider;
    }
}
