<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CriterioValutazioneStudenteSearch - Model di ricerca per CriterioValutazioneStudente
 */
class CriterioValutazioneStudenteSearch extends CriterioValutazioneStudente
{
    public function rules()
    {
        return [
            [['id', 'avviso_id', 'ordine', 'attivo'], 'integer'],
            [['nome', 'descrizione'], 'safe'],
            [['peso', 'punteggio_min', 'punteggio_max'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = CriterioValutazioneStudente::find()->with(['avviso']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['ordine' => SORT_ASC],
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
            'avviso_id' => $this->avviso_id,
            'attivo' => $this->attivo,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome]);

        return $dataProvider;
    }
}
