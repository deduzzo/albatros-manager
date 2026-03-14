<?php

namespace app\models;

use yii\data\ActiveDataProvider;

/**
 * AllievoAuthSearch - Ricerca e filtro credenziali studenti.
 * Usa leftJoin (NO joinWith) per evitare conflitti con SoftDeleteTrait.
 */
class AllievoAuthSearch extends AllievoAuth
{
    /**
     * @var string ricerca full-text
     */
    public $ricerca;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ricerca', 'status'], 'safe'],
            [['totp_enabled'], 'integer'],
        ];
    }

    /**
     * Crea il DataProvider per la ricerca.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AllievoAuth::find()
            ->alias('aa')
            ->leftJoin('allievo a', 'aa.allievo_codice_fiscale = a.codice_fiscale AND a.deleted_at IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['aa.status' => $this->status]);
        $query->andFilterWhere(['aa.totp_enabled' => $this->totp_enabled]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['or',
                ['like', 'aa.allievo_codice_fiscale', $this->ricerca],
                ['like', 'a.cognome', $this->ricerca],
                ['like', 'a.nome', $this->ricerca],
                ['like', 'a.email', $this->ricerca],
            ]);
        }

        return $dataProvider;
    }
}
