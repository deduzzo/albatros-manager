<?php

namespace app\models;

use yii\data\ActiveDataProvider;

class NotificaSearch extends Notifica
{
    public $ricerca;

    public function rules()
    {
        return [
            [['ricerca', 'destinatario_tipo'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Notifica::find()->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['destinatario_tipo' => $this->destinatario_tipo]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['like', 'titolo', $this->ricerca]);
        }

        return $dataProvider;
    }
}
