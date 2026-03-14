<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AssegnazioneSearch - Model di ricerca per Assegnazione
 */
class AssegnazioneSearch extends Assegnazione
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
            [['id', 'modulo_id', 'istanza_id'], 'integer'],
            [['docente_codice_fiscale', 'data_assegnazione', 'delibera_cts', 'data_delibera', 'status', 'note', 'docente_nome', 'modulo_titolo'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Assegnazione::findWithDeleted()
            ->alias('a')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('modulo m', 'a.modulo_id = m.id')
            ->andWhere(['a.deleted_at' => null]);

        // Eager load relazioni per evitare N+1
        $query->with(['contratto', 'istanza', 'docente', 'modulo']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_assegnazione' => SORT_DESC],
                'attributes' => [
                    'id',
                    'modulo_id',
                    'docente_codice_fiscale',
                    'data_assegnazione',
                    'delibera_cts',
                    'data_delibera',
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
            'a.id' => $this->id,
            'a.modulo_id' => $this->modulo_id,
            'a.istanza_id' => $this->istanza_id,
            'a.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'a.docente_codice_fiscale', $this->docente_codice_fiscale])
            ->andFilterWhere(['like', 'a.delibera_cts', $this->delibera_cts])
            ->andFilterWhere(['like', 'a.note', $this->note]);

        if ($this->data_assegnazione) {
            $query->andFilterWhere(['a.data_assegnazione' => $this->data_assegnazione]);
        }
        if ($this->data_delibera) {
            $query->andFilterWhere(['a.data_delibera' => $this->data_delibera]);
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
