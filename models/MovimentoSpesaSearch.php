<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MovimentoSpesaSearch - Model di ricerca per MovimentoSpesa
 */
class MovimentoSpesaSearch extends MovimentoSpesa
{
    /**
     * @var string nome progetto (per ricerca)
     */
    public $progetto_nome;

    /**
     * @var string codice voce di spesa
     */
    public $voce_codice;

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
            [['id', 'progetto_id', 'voce_spesa_id', 'riferimento_id'], 'integer'],
            [['descrizione', 'numero_documento', 'fornitore', 'tipo', 'riferimento_tipo', 'note', 'progetto_nome', 'voce_codice', 'data_da', 'data_a'], 'safe'],
            [['importo'], 'number'],
            [['data_movimento', 'data_documento'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = MovimentoSpesa::findWithDeleted()
            ->alias('ms')
            ->leftJoin('progetto p', 'ms.progetto_id = p.id')
            ->leftJoin('voce_spesa vs', 'ms.voce_spesa_id = vs.id')
            ->leftJoin('voce_spesa_categoria vsc', 'vs.categoria_id = vsc.id')
            ->andWhere(['ms.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_movimento' => SORT_DESC],
                'attributes' => [
                    'id',
                    'progetto_id',
                    'voce_spesa_id',
                    'descrizione',
                    'importo',
                    'data_movimento',
                    'tipo',
                    'fornitore',
                    'created_at',
                    'progetto_nome' => [
                        'asc' => ['p.titolo' => SORT_ASC],
                        'desc' => ['p.titolo' => SORT_DESC],
                        'label' => 'Progetto',
                    ],
                    'voce_codice' => [
                        'asc' => ['vs.codice' => SORT_ASC],
                        'desc' => ['vs.codice' => SORT_DESC],
                        'label' => 'Voce Spesa',
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
            'ms.id' => $this->id,
            'ms.progetto_id' => $this->progetto_id,
            'ms.voce_spesa_id' => $this->voce_spesa_id,
            'ms.tipo' => $this->tipo,
            'ms.riferimento_tipo' => $this->riferimento_tipo,
        ]);

        $query->andFilterWhere(['like', 'ms.descrizione', $this->descrizione])
            ->andFilterWhere(['like', 'ms.numero_documento', $this->numero_documento])
            ->andFilterWhere(['like', 'ms.fornitore', $this->fornitore])
            ->andFilterWhere(['like', 'ms.note', $this->note]);

        // Filtro per nome progetto
        if (!empty($this->progetto_nome)) {
            $query->andFilterWhere(['like', 'p.titolo', $this->progetto_nome]);
        }

        // Filtro per codice voce
        if (!empty($this->voce_codice)) {
            $query->andWhere([
                'or',
                ['like', 'vs.codice', $this->voce_codice],
                ['like', 'vs.nome', $this->voce_codice],
            ]);
        }

        // Filtro per periodo
        if (!empty($this->data_da)) {
            $query->andWhere(['>=', 'ms.data_movimento', $this->data_da]);
        }
        if (!empty($this->data_a)) {
            $query->andWhere(['<=', 'ms.data_movimento', $this->data_a]);
        }

        return $dataProvider;
    }
}
