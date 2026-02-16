<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CalendarioSearch - Model di ricerca per Calendario
 */
class CalendarioSearch extends Calendario
{
    /**
     * @var string ricerca per nome docente
     */
    public $docente_nome;

    /**
     * @var string ricerca per titolo modulo
     */
    public $modulo_titolo;

    public function rules()
    {
        return [
            [['id', 'modulo_id'], 'integer'],
            [['docente_codice_fiscale', 'data', 'ora_inizio', 'ora_fine', 'aula', 'argomento', 'descrizione_attivita', 'status', 'note', 'docente_nome', 'modulo_titolo'], 'safe'],
            [['ore'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Calendario::findWithDeleted()
            ->alias('cal')
            ->leftJoin('modulo m', 'cal.modulo_id = m.id')
            ->leftJoin('docente d', 'cal.docente_codice_fiscale = d.codice_fiscale')
            ->andWhere(['cal.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data' => SORT_DESC],
                'attributes' => [
                    'id',
                    'modulo_id',
                    'docente_codice_fiscale',
                    'data',
                    'ora_inizio',
                    'ora_fine',
                    'ore',
                    'aula',
                    'argomento',
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
            'cal.id' => $this->id,
            'cal.modulo_id' => $this->modulo_id,
            'cal.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'cal.docente_codice_fiscale', $this->docente_codice_fiscale])
            ->andFilterWhere(['like', 'cal.aula', $this->aula])
            ->andFilterWhere(['like', 'cal.argomento', $this->argomento]);

        if ($this->data) {
            $query->andFilterWhere(['cal.data' => $this->data]);
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
