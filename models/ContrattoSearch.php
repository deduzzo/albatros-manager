<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ContrattoSearch - Model di ricerca per Contratto
 */
class ContrattoSearch extends Contratto
{
    /**
     * @var string ricerca per nome docente (via assegnazione)
     */
    public $docente_nome;

    /**
     * @var string ricerca per titolo modulo (via assegnazione)
     */
    public $modulo_titolo;

    public function rules()
    {
        return [
            [['id', 'assegnazione_id', 'ore_contrattualizzate'], 'integer'],
            [['tipo', 'numero_contratto', 'data_stipula', 'data_inizio', 'data_fine', 'data_inizio_lezioni', 'data_fine_lezioni', 'documento_path', 'status', 'note', 'docente_nome', 'modulo_titolo'], 'safe'],
            [['compenso_orario', 'compenso_totale', 'ritenute', 'netto_docente'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Contratto::findWithDeleted()
            ->alias('c')
            ->leftJoin('assegnazione a', 'c.assegnazione_id = a.id')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('modulo m', 'a.modulo_id = m.id')
            ->andWhere(['c.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_stipula' => SORT_DESC],
                'attributes' => [
                    'id',
                    'assegnazione_id',
                    'tipo',
                    'numero_contratto',
                    'data_stipula',
                    'data_inizio',
                    'data_fine',
                    'data_inizio_lezioni',
                    'data_fine_lezioni',
                    'compenso_orario',
                    'ore_contrattualizzate',
                    'compenso_totale',
                    'status',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC],
                        'desc' => ['d.cognome' => SORT_DESC, 'd.nome' => SORT_DESC],
                        'label' => 'Docente',
                    ],
                    'modulo_titolo' => [
                        'asc' => ['m.titolo' => SORT_ASC],
                        'desc' => ['m.titolo' => SORT_DESC],
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
            'c.id' => $this->id,
            'c.assegnazione_id' => $this->assegnazione_id,
            'c.tipo' => $this->tipo,
            'c.status' => $this->status,
            'c.ore_contrattualizzate' => $this->ore_contrattualizzate,
        ]);

        $query->andFilterWhere(['like', 'c.numero_contratto', $this->numero_contratto])
            ->andFilterWhere(['like', 'c.note', $this->note]);

        if ($this->data_stipula) {
            $query->andFilterWhere(['c.data_stipula' => $this->data_stipula]);
        }
        if ($this->data_inizio) {
            $query->andFilterWhere(['>=', 'c.data_inizio', $this->data_inizio]);
        }
        if ($this->data_fine) {
            $query->andFilterWhere(['<=', 'c.data_fine', $this->data_fine]);
        }

        // Filtro per nome docente
        if (!empty($this->docente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'd.cognome', $this->docente_nome],
                ['like', 'd.nome', $this->docente_nome],
            ]);
        }

        // Filtro per titolo modulo
        if (!empty($this->modulo_titolo)) {
            $query->andFilterWhere(['like', 'm.titolo', $this->modulo_titolo]);
        }

        return $dataProvider;
    }
}
