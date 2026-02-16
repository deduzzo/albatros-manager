<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IscrizioneSearch - Model di ricerca per Iscrizione
 */
class IscrizioneSearch extends Iscrizione
{
    /**
     * @var string ricerca per nome allievo
     */
    public $allievo_nome;

    /**
     * @var string ricerca per titolo modulo
     */
    public $modulo_titolo;

    public function rules()
    {
        return [
            [['id', 'modulo_id'], 'integer'],
            [['allievo_codice_fiscale', 'data_iscrizione', 'data_ritiro', 'motivo_ritiro', 'status', 'esito_finale', 'note', 'allievo_nome', 'modulo_titolo'], 'safe'],
            [['percentuale_frequenza'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Iscrizione::findWithDeleted()
            ->alias('i')
            ->leftJoin('modulo m', 'i.modulo_id = m.id')
            ->leftJoin('allievo al', 'i.allievo_codice_fiscale = al.codice_fiscale')
            ->andWhere(['i.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_iscrizione' => SORT_DESC],
                'attributes' => [
                    'id',
                    'modulo_id',
                    'allievo_codice_fiscale',
                    'data_iscrizione',
                    'data_ritiro',
                    'status',
                    'percentuale_frequenza',
                    'esito_finale',
                    'created_at',
                    'allievo_nome' => [
                        'asc' => ['al.cognome' => SORT_ASC, 'al.nome' => SORT_ASC],
                        'desc' => ['al.cognome' => SORT_DESC, 'al.nome' => SORT_DESC],
                        'label' => 'Allievo',
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
            'i.id' => $this->id,
            'i.modulo_id' => $this->modulo_id,
            'i.status' => $this->status,
            'i.esito_finale' => $this->esito_finale,
        ]);

        $query->andFilterWhere(['like', 'i.allievo_codice_fiscale', $this->allievo_codice_fiscale]);

        if ($this->data_iscrizione) {
            $query->andFilterWhere(['i.data_iscrizione' => $this->data_iscrizione]);
        }

        // Filtro per nome allievo
        if (!empty($this->allievo_nome)) {
            $query->andWhere([
                'or',
                ['like', 'al.cognome', $this->allievo_nome],
                ['like', 'al.nome', $this->allievo_nome],
            ]);
        }

        // Filtro per titolo modulo
        if (!empty($this->modulo_titolo)) {
            $query->andFilterWhere(['like', 'm.titolo', $this->modulo_titolo]);
        }

        return $dataProvider;
    }
}
