<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RimborsoSpesaSearch - Model di ricerca per RimborsoSpesa
 */
class RimborsoSpesaSearch extends RimborsoSpesa
{
    /**
     * @var string nome docente
     */
    public $docente_nome;

    /**
     * @var int progetto
     */
    public $progetto_id;

    /**
     * @var string nome modulo
     */
    public $modulo_nome;

    public function rules()
    {
        return [
            [['id', 'assegnazione_id', 'approvato_da', 'progetto_id'], 'integer'],
            [['tipo', 'descrizione', 'data_spesa', 'status', 'note', 'docente_nome', 'modulo_nome'], 'safe'],
            [['importo'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = RimborsoSpesa::findWithDeleted()
            ->alias('rs')
            ->leftJoin('assegnazione a', 'rs.assegnazione_id = a.id')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('modulo m', 'a.modulo_id = m.id')
            ->leftJoin('avviso av', 'm.avviso_id = av.id')
            ->leftJoin('progetto p', 'av.progetto_id = p.id')
            ->andWhere(['rs.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'tipo',
                    'importo',
                    'data_spesa',
                    'status',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC],
                        'desc' => ['d.cognome' => SORT_DESC, 'd.nome' => SORT_DESC],
                        'label' => 'Docente',
                    ],
                    'modulo_nome' => [
                        'asc' => ['m.nome' => SORT_ASC],
                        'desc' => ['m.nome' => SORT_DESC],
                        'label' => 'Modulo',
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
            'rs.id' => $this->id,
            'rs.assegnazione_id' => $this->assegnazione_id,
            'rs.tipo' => $this->tipo,
            'rs.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'rs.descrizione', $this->descrizione])
            ->andFilterWhere(['like', 'rs.note', $this->note]);

        if ($this->data_spesa) {
            $query->andFilterWhere(['rs.data_spesa' => $this->data_spesa]);
        }

        // Filtro per nome docente
        if (!empty($this->docente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'd.cognome', $this->docente_nome],
                ['like', 'd.nome', $this->docente_nome],
            ]);
        }

        // Filtro per progetto
        if (!empty($this->progetto_id)) {
            $query->andWhere(['p.id' => $this->progetto_id]);
        }

        // Filtro per modulo
        if (!empty($this->modulo_nome)) {
            $query->andFilterWhere(['like', 'm.nome', $this->modulo_nome]);
        }

        return $dataProvider;
    }
}
