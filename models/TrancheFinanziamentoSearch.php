<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TrancheFinanziamentoSearch - Model di ricerca per TrancheFinanziamento
 */
class TrancheFinanziamentoSearch extends TrancheFinanziamento
{
    /**
     * @var string nome progetto
     */
    public $progetto_nome;

    /**
     * @var string data inizio periodo
     */
    public $data_da;

    /**
     * @var string data fine periodo
     */
    public $data_a;

    public function rules()
    {
        return [
            [['id', 'progetto_id', 'numero_tranche'], 'integer'],
            [['status', 'riferimento', 'note', 'progetto_nome', 'data_da', 'data_a'], 'safe'],
            [['importo'], 'number'],
            [['data_accredito', 'data_prevista'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = TrancheFinanziamento::findWithDeleted()
            ->alias('tf')
            ->leftJoin('progetto p', 'tf.progetto_id = p.id')
            ->andWhere(['tf.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'progetto_id',
                    'importo',
                    'data_accredito',
                    'data_prevista',
                    'numero_tranche',
                    'status',
                    'created_at',
                    'progetto_nome' => [
                        'asc' => ['p.titolo' => SORT_ASC],
                        'desc' => ['p.titolo' => SORT_DESC],
                        'label' => 'Progetto',
                    ],
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
            'tf.id' => $this->id,
            'tf.progetto_id' => $this->progetto_id,
            'tf.numero_tranche' => $this->numero_tranche,
            'tf.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'tf.riferimento', $this->riferimento])
            ->andFilterWhere(['like', 'tf.note', $this->note]);

        // Filtro per nome progetto
        if (!empty($this->progetto_nome)) {
            $query->andFilterWhere(['like', 'p.titolo', $this->progetto_nome]);
        }

        // Filtro per periodo accredito
        if (!empty($this->data_da)) {
            $query->andWhere(['>=', 'tf.data_accredito', $this->data_da]);
        }
        if (!empty($this->data_a)) {
            $query->andWhere(['<=', 'tf.data_accredito', $this->data_a]);
        }

        return $dataProvider;
    }
}
