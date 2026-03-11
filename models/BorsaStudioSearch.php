<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BorsaStudioSearch - Model di ricerca per BorsaStudio
 */
class BorsaStudioSearch extends BorsaStudio
{
    /**
     * @var string nome studente
     */
    public $studente_nome;

    /**
     * @var string nome progetto
     */
    public $progetto_nome;

    public function rules()
    {
        return [
            [['id', 'progetto_id'], 'integer'],
            [['allievo_codice_fiscale', 'anno_formativo', 'tipologia_sede', 'status', 'note', 'studente_nome', 'progetto_nome'], 'safe'],
            [['importo_finale', 'totale_erogato', 'isee_importo'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = BorsaStudio::findWithDeleted()
            ->alias('bs')
            ->leftJoin('allievo al', 'bs.allievo_codice_fiscale = al.codice_fiscale')
            ->leftJoin('progetto p', 'bs.progetto_id = p.id')
            ->andWhere(['bs.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'anno_formativo',
                    'tipologia_sede',
                    'importo_finale',
                    'totale_erogato',
                    'status',
                    'created_at',
                    'studente_nome' => [
                        'asc' => ['al.cognome' => SORT_ASC, 'al.nome' => SORT_ASC],
                        'desc' => ['al.cognome' => SORT_DESC, 'al.nome' => SORT_DESC],
                        'label' => 'Studente',
                    ],
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
            'bs.id' => $this->id,
            'bs.progetto_id' => $this->progetto_id,
            'bs.tipologia_sede' => $this->tipologia_sede,
            'bs.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'bs.anno_formativo', $this->anno_formativo])
            ->andFilterWhere(['like', 'bs.allievo_codice_fiscale', $this->allievo_codice_fiscale])
            ->andFilterWhere(['like', 'bs.note', $this->note]);

        // Filtro per nome studente
        if (!empty($this->studente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'al.cognome', $this->studente_nome],
                ['like', 'al.nome', $this->studente_nome],
            ]);
        }

        // Filtro per nome progetto
        if (!empty($this->progetto_nome)) {
            $query->andFilterWhere(['like', 'p.titolo', $this->progetto_nome]);
        }

        return $dataProvider;
    }
}
