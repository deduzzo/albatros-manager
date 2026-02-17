<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TagCurriculumSearch - Model di ricerca per TagCurriculum
 */
class TagCurriculumSearch extends TagCurriculum
{
    public function rules()
    {
        return [
            [['id', 'categoria_id', 'ordine'], 'integer'],
            [['codice', 'nome', 'descrizione', 'keywords'], 'safe'],
            [['attivo'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = TagCurriculum::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['categoria_id' => SORT_ASC, 'ordine' => SORT_ASC],
                'attributes' => [
                    'id',
                    'categoria_id',
                    'codice',
                    'nome',
                    'descrizione',
                    'keywords',
                    'ordine',
                    'attivo',
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
            'categoria_id' => $this->categoria_id,
            'ordine' => $this->ordine,
            'attivo' => $this->attivo,
        ]);

        $query->andFilterWhere(['like', 'codice', $this->codice])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione])
            ->andFilterWhere(['like', 'keywords', $this->keywords]);

        return $dataProvider;
    }
}
