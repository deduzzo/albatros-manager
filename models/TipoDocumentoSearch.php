<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TipoDocumentoSearch - Model di ricerca per TipoDocumento
 */
class TipoDocumentoSearch extends TipoDocumento
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['codice', 'descrizione'], 'safe'],
            [['richiede_scadenza', 'attivo'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = TipoDocumento::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['codice' => SORT_ASC],
                'attributes' => [
                    'id',
                    'codice',
                    'descrizione',
                    'richiede_scadenza',
                    'attivo',
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
            'id' => $this->id,
            'richiede_scadenza' => $this->richiede_scadenza,
            'attivo' => $this->attivo,
        ]);

        $query->andFilterWhere(['like', 'codice', $this->codice])
            ->andFilterWhere(['like', 'descrizione', $this->descrizione]);

        return $dataProvider;
    }
}
