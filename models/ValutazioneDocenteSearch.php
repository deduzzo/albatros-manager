<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ValutazioneDocenteSearch - Model di ricerca per ValutazioneDocente
 */
class ValutazioneDocenteSearch extends ValutazioneDocente
{
    /**
     * @var string ricerca per nome docente
     */
    public $docente_nome;

    /**
     * @var string ricerca per titolo modulo
     */
    public $modulo_titolo;

    /**
     * @var string ricerca per nome studente
     */
    public $studente_nome;

    /**
     * @var string filtro per sorgente tipo
     */
    public $sorgente_tipo;

    public function rules()
    {
        return [
            [['id', 'modulo_id', 'punteggio_chiarezza', 'punteggio_competenza', 'punteggio_disponibilita', 'punteggio_materiali', 'punteggio_globale'], 'integer'],
            [['docente_codice_fiscale', 'commento', 'data_valutazione', 'docente_nome', 'modulo_titolo', 'studente_nome', 'sorgente_tipo'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = ValutazioneDocente::findWithDeleted()
            ->alias('v')
            ->leftJoin('modulo m', 'v.modulo_id = m.id')
            ->leftJoin('docente d', 'v.docente_codice_fiscale = d.codice_fiscale')
            ->andWhere(['v.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_valutazione' => SORT_DESC],
                'attributes' => [
                    'id',
                    'modulo_id',
                    'docente_codice_fiscale',
                    'punteggio_chiarezza',
                    'punteggio_competenza',
                    'punteggio_disponibilita',
                    'punteggio_materiali',
                    'punteggio_globale',
                    'data_valutazione',
                    'created_at',
                    'sorgente_tipo',
                    'studente_nome' => [
                        'asc' => ['v.studente_nome' => SORT_ASC],
                        'desc' => ['v.studente_nome' => SORT_DESC],
                        'label' => 'Studente',
                    ],
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
            'v.id' => $this->id,
            'v.modulo_id' => $this->modulo_id,
            'v.punteggio_chiarezza' => $this->punteggio_chiarezza,
            'v.punteggio_competenza' => $this->punteggio_competenza,
            'v.punteggio_disponibilita' => $this->punteggio_disponibilita,
            'v.punteggio_materiali' => $this->punteggio_materiali,
            'v.punteggio_globale' => $this->punteggio_globale,
        ]);

        $query->andFilterWhere(['like', 'v.docente_codice_fiscale', $this->docente_codice_fiscale])
            ->andFilterWhere(['like', 'v.commento', $this->commento]);

        if ($this->data_valutazione) {
            $query->andFilterWhere(['v.data_valutazione' => $this->data_valutazione]);
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

        // Filtro per nome studente
        if (!empty($this->studente_nome)) {
            $query->andFilterWhere(['like', 'v.studente_nome', $this->studente_nome]);
        }

        // Filtro per sorgente tipo
        if (!empty($this->sorgente_tipo)) {
            $query->andFilterWhere(['v.sorgente_tipo' => $this->sorgente_tipo]);
        }

        return $dataProvider;
    }
}
