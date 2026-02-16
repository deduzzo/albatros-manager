<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PagamentoSearch - Model di ricerca per Pagamento
 */
class PagamentoSearch extends Pagamento
{
    /**
     * @var string ricerca per nome docente (via contratto -> assegnazione)
     */
    public $docente_nome;

    /**
     * @var string ricerca per numero contratto
     */
    public $numero_contratto;

    public function rules()
    {
        return [
            [['id', 'contratto_id'], 'integer'],
            [['data_pagamento', 'data_scadenza', 'tipo', 'riferimento_fattura', 'numero_mandato', 'cro_bonifico', 'status', 'note', 'docente_nome', 'numero_contratto'], 'safe'],
            [['importo_lordo', 'ritenute', 'importo_netto'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Pagamento::findWithDeleted()
            ->alias('p')
            ->leftJoin('contratto ct', 'p.contratto_id = ct.id')
            ->leftJoin('assegnazione a', 'ct.assegnazione_id = a.id')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->andWhere(['p.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'contratto_id',
                    'importo_lordo',
                    'ritenute',
                    'importo_netto',
                    'data_pagamento',
                    'data_scadenza',
                    'tipo',
                    'riferimento_fattura',
                    'numero_mandato',
                    'cro_bonifico',
                    'status',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC],
                        'desc' => ['d.cognome' => SORT_DESC, 'd.nome' => SORT_DESC],
                        'label' => 'Docente',
                    ],
                    'numero_contratto' => [
                        'asc' => ['ct.numero_contratto' => SORT_ASC],
                        'desc' => ['ct.numero_contratto' => SORT_DESC],
                        'label' => 'N. Contratto',
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
            'p.id' => $this->id,
            'p.contratto_id' => $this->contratto_id,
            'p.tipo' => $this->tipo,
            'p.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'p.riferimento_fattura', $this->riferimento_fattura])
            ->andFilterWhere(['like', 'p.numero_mandato', $this->numero_mandato])
            ->andFilterWhere(['like', 'p.cro_bonifico', $this->cro_bonifico])
            ->andFilterWhere(['like', 'p.note', $this->note]);

        if ($this->data_pagamento) {
            $query->andFilterWhere(['p.data_pagamento' => $this->data_pagamento]);
        }
        if ($this->data_scadenza) {
            $query->andFilterWhere(['p.data_scadenza' => $this->data_scadenza]);
        }

        // Filtro per nome docente
        if (!empty($this->docente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'd.cognome', $this->docente_nome],
                ['like', 'd.nome', $this->docente_nome],
            ]);
        }

        // Filtro per numero contratto
        if (!empty($this->numero_contratto)) {
            $query->andFilterWhere(['like', 'ct.numero_contratto', $this->numero_contratto]);
        }

        return $dataProvider;
    }
}
