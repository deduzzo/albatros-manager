<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProgettoSearch - Model di ricerca per Progetto
 */
class ProgettoSearch extends Progetto
{
    public function rules()
    {
        return [
            [['id', 'fonte_finanziamento_id', 'conto_corrente_id'], 'integer'],
            [['codice_progetto', 'titolo', 'descrizione', 'status', 'data_inizio', 'data_fine'], 'safe'],
            [['budget_totale'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Progetto::find()->with(['fonteFinanziamento']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['codice_progetto' => SORT_ASC],
                'attributes' => [
                    'id',
                    'codice_progetto',
                    'titolo',
                    'fonte_finanziamento_id',
                    'budget_totale',
                    'data_inizio',
                    'data_fine',
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
            'fonte_finanziamento_id' => $this->fonte_finanziamento_id,
            'conto_corrente_id' => $this->conto_corrente_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'codice_progetto', $this->codice_progetto])
            ->andFilterWhere(['like', 'titolo', $this->titolo])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione]);

        if ($this->data_inizio) {
            $query->andFilterWhere(['>=', 'data_inizio', $this->data_inizio]);
        }
        if ($this->data_fine) {
            $query->andFilterWhere(['<=', 'data_fine', $this->data_fine]);
        }

        return $dataProvider;
    }
}
