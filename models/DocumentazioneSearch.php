<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentazioneSearch - Model di ricerca per Documentazione
 */
class DocumentazioneSearch extends Documentazione
{
    /**
     * @var string ricerca per nome docente
     */
    public $docente_nome;

    /**
     * @var string ricerca per nome allievo
     */
    public $allievo_nome;

    /**
     * @var string ricerca per tipo documento descrizione
     */
    public $tipo_documento_descrizione;

    public function rules()
    {
        return [
            [['id', 'tipo_documento_id', 'file_size'], 'integer'],
            [['docente_codice_fiscale', 'allievo_codice_fiscale', 'titolo', 'descrizione', 'file_nome_originale', 'file_mime_type', 'data_documento', 'data_scadenza', 'status', 'note', 'docente_nome', 'allievo_nome', 'tipo_documento_descrizione'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Documentazione::findWithDeleted()
            ->alias('doc')
            ->leftJoin('tipo_documento td', 'doc.tipo_documento_id = td.id')
            ->leftJoin('docente d', 'doc.docente_codice_fiscale = d.codice_fiscale')
            ->leftJoin('allievo al', 'doc.allievo_codice_fiscale = al.codice_fiscale')
            ->andWhere(['doc.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'tipo_documento_id',
                    'titolo',
                    'data_documento',
                    'data_scadenza',
                    'status',
                    'file_size',
                    'created_at',
                    'docente_nome' => [
                        'asc' => ['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC],
                        'desc' => ['d.cognome' => SORT_DESC, 'd.nome' => SORT_DESC],
                        'label' => 'Docente',
                    ],
                    'allievo_nome' => [
                        'asc' => ['al.cognome' => SORT_ASC, 'al.nome' => SORT_ASC],
                        'desc' => ['al.cognome' => SORT_DESC, 'al.nome' => SORT_DESC],
                        'label' => 'Allievo',
                    ],
                    'tipo_documento_descrizione' => [
                        'asc' => ['td.descrizione' => SORT_ASC],
                        'desc' => ['td.descrizione' => SORT_DESC],
                        'label' => 'Tipo Documento',
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
            'doc.id' => $this->id,
            'doc.tipo_documento_id' => $this->tipo_documento_id,
            'doc.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'doc.titolo', $this->titolo])
            ->andFilterWhere(['like', 'doc.descrizione', $this->descrizione])
            ->andFilterWhere(['like', 'doc.file_nome_originale', $this->file_nome_originale])
            ->andFilterWhere(['like', 'doc.docente_codice_fiscale', $this->docente_codice_fiscale])
            ->andFilterWhere(['like', 'doc.allievo_codice_fiscale', $this->allievo_codice_fiscale]);

        if ($this->data_documento) {
            $query->andFilterWhere(['doc.data_documento' => $this->data_documento]);
        }
        if ($this->data_scadenza) {
            $query->andFilterWhere(['doc.data_scadenza' => $this->data_scadenza]);
        }

        // Filtro per nome docente
        if (!empty($this->docente_nome)) {
            $query->andWhere([
                'or',
                ['like', 'd.cognome', $this->docente_nome],
                ['like', 'd.nome', $this->docente_nome],
            ]);
        }

        // Filtro per nome allievo
        if (!empty($this->allievo_nome)) {
            $query->andWhere([
                'or',
                ['like', 'al.cognome', $this->allievo_nome],
                ['like', 'al.nome', $this->allievo_nome],
            ]);
        }

        // Filtro per tipo documento
        if (!empty($this->tipo_documento_descrizione)) {
            $query->andFilterWhere(['like', 'td.descrizione', $this->tipo_documento_descrizione]);
        }

        return $dataProvider;
    }
}
