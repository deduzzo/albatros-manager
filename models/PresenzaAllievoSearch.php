<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PresenzaAllievoSearch - Model di ricerca per PresenzaAllievo
 */
class PresenzaAllievoSearch extends PresenzaAllievo
{
    /**
     * @var string ricerca per nome allievo
     */
    public $allievo_nome;

    /**
     * @var string ricerca per data lezione (via calendario)
     */
    public $calendario_data;

    /**
     * @var string ricerca per titolo modulo (via calendario -> modulo)
     */
    public $modulo_titolo;

    public function rules()
    {
        return [
            [['id', 'calendario_id'], 'integer'],
            [['allievo_codice_fiscale', 'ora_ingresso', 'ora_uscita', 'note', 'allievo_nome', 'calendario_data', 'modulo_titolo'], 'safe'],
            [['presente', 'giustificato'], 'boolean'],
            [['ore_presenza'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = PresenzaAllievo::findWithDeleted()
            ->alias('pa')
            ->leftJoin('calendario cal', 'pa.calendario_id = cal.id')
            ->leftJoin('modulo m', 'cal.modulo_id = m.id')
            ->leftJoin('allievo al', 'pa.allievo_codice_fiscale = al.codice_fiscale')
            ->andWhere(['pa.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'calendario_id',
                    'allievo_codice_fiscale',
                    'presente',
                    'ora_ingresso',
                    'ora_uscita',
                    'ore_presenza',
                    'giustificato',
                    'created_at',
                    'allievo_nome' => [
                        'asc' => ['al.cognome' => SORT_ASC, 'al.nome' => SORT_ASC],
                        'desc' => ['al.cognome' => SORT_DESC, 'al.nome' => SORT_DESC],
                        'label' => 'Allievo',
                    ],
                    'calendario_data' => [
                        'asc' => ['cal.data' => SORT_ASC],
                        'desc' => ['cal.data' => SORT_DESC],
                        'label' => 'Data Lezione',
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
            'pa.id' => $this->id,
            'pa.calendario_id' => $this->calendario_id,
            'pa.presente' => $this->presente,
            'pa.giustificato' => $this->giustificato,
        ]);

        $query->andFilterWhere(['like', 'pa.allievo_codice_fiscale', $this->allievo_codice_fiscale]);

        // Filtro per nome allievo
        if (!empty($this->allievo_nome)) {
            $query->andWhere([
                'or',
                ['like', 'al.cognome', $this->allievo_nome],
                ['like', 'al.nome', $this->allievo_nome],
            ]);
        }

        // Filtro per data lezione
        if (!empty($this->calendario_data)) {
            $query->andFilterWhere(['cal.data' => $this->calendario_data]);
        }

        // Filtro per titolo modulo
        if (!empty($this->modulo_titolo)) {
            $query->andFilterWhere(['like', 'm.titolo', $this->modulo_titolo]);
        }

        return $dataProvider;
    }
}
