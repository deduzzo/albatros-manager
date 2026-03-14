<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AllievoSearch - Model di ricerca per Allievo
 */
class AllievoSearch extends Allievo
{
    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome', 'data_nascita', 'luogo_nascita', 'indirizzo', 'cap', 'citta', 'provincia', 'telefono', 'email', 'note'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Allievo::find()->with(['iscrizioni']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['cognome' => SORT_ASC, 'nome' => SORT_ASC],
                'attributes' => [
                    'codice_fiscale',
                    'cognome',
                    'nome',
                    'data_nascita',
                    'luogo_nascita',
                    'citta',
                    'provincia',
                    'telefono',
                    'email',
                    'created_at',
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
            'provincia' => $this->provincia,
        ]);

        $query->andFilterWhere(['like', 'codice_fiscale', $this->codice_fiscale])
            ->andFilterWhere(['like', 'cognome', $this->cognome])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'luogo_nascita', $this->luogo_nascita])
            ->andFilterWhere(['like', 'citta', $this->citta])
            ->andFilterWhere(['like', 'telefono', $this->telefono])
            ->andFilterWhere(['like', 'email', $this->email]);

        if ($this->data_nascita) {
            $query->andFilterWhere(['data_nascita' => $this->data_nascita]);
        }

        return $dataProvider;
    }
}
