<?php

namespace app\models;

use yii\data\ActiveDataProvider;

/**
 * DocenteAuthSearch - Ricerca e filtro credenziali docenti.
 * Usa leftJoin (NO joinWith) per evitare conflitti con SoftDeleteTrait.
 */
class DocenteAuthSearch extends DocenteAuth
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
        // Usare findWithDeleted() + filtro manuale per evitare conflitto alias con SoftDeleteTrait
        $query = DocenteAuth::findWithDeleted()
            ->alias('da')
            ->andWhere(['da.deleted_at' => null])
            ->leftJoin('docente d', 'da.docente_codice_fiscale = d.codice_fiscale AND d.deleted_at IS NULL');

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

        $query->andFilterWhere(['da.status' => $this->status]);
        $query->andFilterWhere(['da.totp_enabled' => $this->totp_enabled]);

        if (!empty($this->ricerca)) {
            $query->andWhere(['or',
                ['like', 'da.docente_codice_fiscale', $this->ricerca],
                ['like', 'd.cognome', $this->ricerca],
                ['like', 'd.nome', $this->ricerca],
                ['like', 'd.email', $this->ricerca],
            ]);
        }

        return $dataProvider;
    }
}
