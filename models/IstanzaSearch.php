<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IstanzaSearch - Model di ricerca per Istanza
 */
class IstanzaSearch extends Istanza
{
    public $docente_nome;

    public function rules()
    {
        return [
            [['id', 'avviso_id', 'modulo_id', 'posizione_graduatoria'], 'integer'],
            [['docente_codice_fiscale', 'docente_nome', 'status', 'data_presentazione'], 'safe'],
            [['punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Istanza::find()
            ->joinWith(['docente', 'avviso', 'modulo']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['data_presentazione' => SORT_DESC],
                'attributes' => [
                    'id',
                    'avviso_id',
                    'modulo_id',
                    'docente_codice_fiscale',
                    'data_presentazione',
                    'punteggio_totale',
                    'posizione_graduatoria',
                    'status',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['docente.cognome' => SORT_ASC, 'docente.nome' => SORT_ASC],
                        'desc' => ['docente.cognome' => SORT_DESC, 'docente.nome' => SORT_DESC],
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
            'istanza.id' => $this->id,
            'istanza.avviso_id' => $this->avviso_id,
            'istanza.modulo_id' => $this->modulo_id,
            'istanza.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'istanza.docente_codice_fiscale', $this->docente_codice_fiscale]);

        if ($this->docente_nome) {
            $query->andWhere([
                'or',
                ['like', 'docente.cognome', $this->docente_nome],
                ['like', 'docente.nome', $this->docente_nome],
                ['like', "CONCAT(docente.cognome, ' ', docente.nome)", $this->docente_nome],
            ]);
        }

        if ($this->data_presentazione) {
            $query->andFilterWhere(['>=', 'istanza.data_presentazione', $this->data_presentazione]);
        }

        return $dataProvider;
    }
}
