<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CriterioValutazioneDocenteSearch - Model di ricerca per CriterioValutazioneDocente
 */
class CriterioValutazioneDocenteSearch extends CriterioValutazioneDocente
{
    public function rules()
    {
        return [
            [['id', 'scala_min', 'scala_max', 'ordinamento', 'attivo'], 'integer'],
            [['codice', 'nome', 'descrizione'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = CriterioValutazioneDocente::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['ordinamento' => SORT_ASC],
                'attributes' => [
                    'id',
                    'codice',
                    'nome',
                    'scala_min',
                    'scala_max',
                    'ordinamento',
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
            'scala_min' => $this->scala_min,
            'scala_max' => $this->scala_max,
            'ordinamento' => $this->ordinamento,
            'attivo' => $this->attivo,
        ]);

        $query->andFilterWhere(['like', 'codice', $this->codice])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione]);

        return $dataProvider;
    }
}
