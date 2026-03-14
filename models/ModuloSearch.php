<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ModuloSearch - Model di ricerca per Modulo
 */
class ModuloSearch extends Modulo
{
    public function rules()
    {
        return [
            [['id', 'avviso_id', 'sede_id', 'ore_totali', 'max_allievi'], 'integer'],
            [['codice', 'titolo', 'descrizione', 'status', 'data_inizio', 'data_fine'], 'safe'],
            [['compenso_orario'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Modulo::find()->with(['avviso', 'sede', 'assegnazioni', 'assegnazioni.docente', 'iscrizioni']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['codice' => SORT_ASC],
                'attributes' => [
                    'id',
                    'codice',
                    'titolo',
                    'avviso_id',
                    'sede_id',
                    'ore_totali',
                    'compenso_orario',
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
            'avviso_id' => $this->avviso_id,
            'sede_id' => $this->sede_id,
            'ore_totali' => $this->ore_totali,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'codice', $this->codice])
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
