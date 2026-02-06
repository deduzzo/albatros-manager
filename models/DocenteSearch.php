<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * DocenteSearch per la ricerca e filtro dei docenti.
 */
class DocenteSearch extends Docente
{
    /**
     * @var string ricerca full-text
     */
    public $ricerca;

    /**
     * @var int filtro per tag
     */
    public $tag_id;

    /**
     * @var int filtro per categoria tag
     */
    public $categoria_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome', 'email', 'citta', 'provincia', 'titolo_studio', 'ricerca'], 'safe'],
            [['tag_id', 'categoria_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ricerca' => 'Ricerca',
            'tag_id' => 'Tag',
            'categoria_id' => 'Categoria',
        ]);
    }

    /**
     * Crea il DataProvider per la ricerca.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Docente::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['cognome' => SORT_ASC, 'nome' => SORT_ASC],
                'attributes' => [
                    'codice_fiscale',
                    'cognome',
                    'nome',
                    'email',
                    'citta',
                    'provincia',
                    'titolo_studio',
                    'created_at',
                    'updated_at',
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

        // Filtri esatti
        $query->andFilterWhere([
            'provincia' => $this->provincia,
        ]);

        // Filtri con LIKE
        $query->andFilterWhere(['like', 'codice_fiscale', $this->codice_fiscale])
            ->andFilterWhere(['like', 'cognome', $this->cognome])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'citta', $this->citta])
            ->andFilterWhere(['like', 'titolo_studio', $this->titolo_studio]);

        // Ricerca full-text (cerca in più campi)
        if (!empty($this->ricerca)) {
            $query->andWhere([
                'or',
                ['like', 'codice_fiscale', $this->ricerca],
                ['like', 'cognome', $this->ricerca],
                ['like', 'nome', $this->ricerca],
                ['like', 'email', $this->ricerca],
                ['like', 'citta', $this->ricerca],
                ['like', 'titolo_studio', $this->ricerca],
                ['like', 'specializzazioni', $this->ricerca],
            ]);
        }

        // Filtro per tag
        if (!empty($this->tag_id)) {
            $query->innerJoin('docente_tag dt', 'dt.docente_codice_fiscale = docente.codice_fiscale')
                ->andWhere(['dt.tag_id' => $this->tag_id]);
        }

        // Filtro per categoria tag
        if (!empty($this->categoria_id)) {
            $query->innerJoin('docente_tag dt2', 'dt2.docente_codice_fiscale = docente.codice_fiscale')
                ->innerJoin('tag_curriculum tc', 'tc.id = dt2.tag_id')
                ->andWhere(['tc.categoria_id' => $this->categoria_id]);
            $query->distinct();
        }

        return $dataProvider;
    }

    /**
     * Cerca docenti con un tag specifico
     *
     * @param int $tagId
     * @return ActiveDataProvider
     */
    public function searchByTag($tagId)
    {
        $query = Docente::find()
            ->innerJoin('docente_tag dt', 'dt.docente_codice_fiscale = docente.codice_fiscale')
            ->where(['dt.tag_id' => $tagId]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['cognome' => SORT_ASC, 'nome' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }
}
