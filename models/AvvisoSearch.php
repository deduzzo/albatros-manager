<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AvvisoSearch - Model di ricerca per Avviso
 */
class AvvisoSearch extends Avviso
{
    public function rules()
    {
        return [
            [['id', 'progetto_id'], 'integer'],
            [['codice_avviso', 'titolo', 'descrizione', 'status', 'data_pubblicazione', 'data_scadenza'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Avviso::find()->with(['progetto', 'istanze', 'moduli']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_scadenza' => SORT_DESC],
                'attributes' => [
                    'id',
                    'codice_avviso',
                    'titolo',
                    'progetto_id',
                    'data_pubblicazione',
                    'data_scadenza',
                    'status',
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
            'progetto_id' => $this->progetto_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'codice_avviso', $this->codice_avviso])
            ->andFilterWhere(['like', 'titolo', $this->titolo])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione]);

        if ($this->data_pubblicazione) {
            $query->andFilterWhere(['>=', 'data_pubblicazione', $this->data_pubblicazione]);
        }
        if ($this->data_scadenza) {
            $query->andFilterWhere(['<=', 'data_scadenza', $this->data_scadenza]);
        }

        return $dataProvider;
    }
}
