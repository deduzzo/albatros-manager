<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TagCategoriaSearch - Model di ricerca per TagCategoria
 */
class TagCategoriaSearch extends TagCategoria
{
    public function rules()
    {
        return [
            [['id', 'ordine'], 'integer'],
            [['nome', 'descrizione', 'colore', 'icona'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = TagCategoria::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['ordine' => SORT_ASC],
                'attributes' => [
                    'id',
                    'nome',
                    'descrizione',
                    'colore',
                    'icona',
                    'ordine',
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
            'ordine' => $this->ordine,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione])
            ->andFilterWhere(['like', 'colore', $this->colore])
            ->andFilterWhere(['like', 'icona', $this->icona]);

        return $dataProvider;
    }
}
