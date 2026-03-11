<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RichiestaFatturazioneSearch - Model di ricerca per RichiestaFatturazione
 */
class RichiestaFatturazioneSearch extends RichiestaFatturazione
{
    /**
     * @var string nome docente
     */
    public $docente_nome;

    /**
     * @var string numero contratto
     */
    public $numero_contratto;

    /**
     * @var int progetto
     */
    public $progetto_id;

    public function rules()
    {
        return [
            [['id', 'contratto_id', 'progetto_id'], 'integer'],
            [['tipo', 'periodo_riferimento', 'data_richiesta', 'data_scadenza', 'destinatario_email', 'status', 'note', 'docente_nome', 'numero_contratto'], 'safe'],
            [['importo'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = RichiestaFatturazione::findWithDeleted()
            ->alias('rf')
            ->leftJoin('contratto ct', 'rf.contratto_id = ct.id')
            ->leftJoin('assegnazione a', 'ct.assegnazione_id = a.id')
            ->leftJoin('docente d', 'a.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('modulo m', 'a.modulo_id = m.id')
            ->leftJoin('avviso av', 'm.avviso_id = av.id')
            ->leftJoin('progetto p', 'av.progetto_id = p.id')
            ->andWhere(['rf.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'tipo',
                    'importo',
                    'data_richiesta',
                    'data_scadenza',
                    'status',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC],
                        'desc' => ['d.cognome' => SORT_DESC, 'd.nome' => SORT_DESC],
                        'label' => 'Docente',
                    ],
                    'numero_contratto' => [
                        'asc' => ['ct.numero_contratto' => SORT_ASC],
                        'desc' => ['ct.numero_contratto' => SORT_DESC],
                        'label' => 'N. Contratto',
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
            'rf.id' => $this->id,
            'rf.contratto_id' => $this->contratto_id,
            'rf.tipo' => $this->tipo,
            'rf.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'rf.periodo_riferimento', $this->periodo_riferimento])
            ->andFilterWhere(['like', 'rf.destinatario_email', $this->destinatario_email])
            ->andFilterWhere(['like', 'rf.note', $this->note]);

        if ($this->data_richiesta) {
            $query->andFilterWhere(['rf.data_richiesta' => $this->data_richiesta]);
        }

        // Filtro per nome docente
        if (!empty($this->docente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'd.cognome', $this->docente_nome],
                ['like', 'd.nome', $this->docente_nome],
            ]);
        }

        // Filtro per numero contratto
        if (!empty($this->numero_contratto)) {
            $query->andFilterWhere(['like', 'ct.numero_contratto', $this->numero_contratto]);
        }

        // Filtro per progetto
        if (!empty($this->progetto_id)) {
            $query->andWhere(['p.id' => $this->progetto_id]);
        }

        return $dataProvider;
    }
}
