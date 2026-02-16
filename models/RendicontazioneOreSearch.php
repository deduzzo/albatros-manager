<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RendicontazioneOreSearch - Model di ricerca per RendicontazioneOre
 */
class RendicontazioneOreSearch extends RendicontazioneOre
{
    public function rules()
    {
        return [
            [['id', 'assegnazione_id', 'firma_digitale', 'approvato_da'], 'integer'],
            [['data', 'status', 'note', 'motivo_rifiuto', 'ora_inizio', 'ora_fine', 'approvato_at'], 'safe'],
            [['ore'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Ricerca rendicontazioni per una specifica assegnazione
     * @param array $params
     * @param int|null $assegnazioneId
     * @return ActiveDataProvider
     */
    public function search($params, $assegnazioneId = null)
    {
        $query = RendicontazioneOre::find();

        if ($assegnazioneId !== null) {
            $query->andWhere(['assegnazione_id' => $assegnazioneId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data' => SORT_DESC],
                'attributes' => [
                    'id',
                    'data',
                    'ore',
                    'ora_inizio',
                    'ora_fine',
                    'firma_digitale',
                    'status',
                    'created_at',
                    'approvato_at',
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
            'assegnazione_id' => $this->assegnazione_id,
            'firma_digitale' => $this->firma_digitale,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'note', $this->note]);

        if ($this->data) {
            $query->andFilterWhere(['data' => $this->data]);
        }

        return $dataProvider;
    }
}
