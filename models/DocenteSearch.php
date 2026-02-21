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
     * @var string|null filtro per esito CTS (per long list)
     */
    public $esito_cts;

    /**
     * @var int|null filtro per numero minimo contratti
     */
    public $min_contratti;

    /**
     * @var float|null filtro per valutazione minima
     */
    public $min_valutazione;

    /**
     * @var float|null valutazione media calcolata (per sorting/filtering in griglia)
     */
    public $valutazione_media;

    /**
     * @var int|null numero contratti calcolato (per sorting/filtering in griglia)
     */
    public $num_contratti;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome', 'email', 'citta', 'provincia', 'titolo_studio', 'ricerca', 'provenienza', 'esito_cts'], 'safe'],
            [['tag_id', 'categoria_id', 'min_contratti', 'num_contratti'], 'integer'],
            [['min_valutazione', 'valutazione_media'], 'number'],
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
            'esito_cts' => 'Esito CTS',
            'min_contratti' => 'Minimo Contratti',
            'min_valutazione' => 'Valutazione Minima',
            'valutazione_media' => 'Valutazione',
            'num_contratti' => 'Contratti',
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

        // Subquery per valutazione media
        $subQueryVal = (new \yii\db\Query())
            ->select('AVG(vd.punteggio_globale)')
            ->from('valutazione_docente vd')
            ->where('vd.docente_codice_fiscale = docente.codice_fiscale')
            ->andWhere(['vd.deleted_at' => null]);

        // Subquery per numero contratti
        $subQueryContr = (new \yii\db\Query())
            ->select('COUNT(c.id)')
            ->from('contratto c')
            ->leftJoin('assegnazione a', 'a.id = c.assegnazione_id AND a.deleted_at IS NULL')
            ->where('a.docente_codice_fiscale = docente.codice_fiscale')
            ->andWhere(['c.deleted_at' => null]);

        $query->addSelect([
            'docente.*',
            'valutazione_media' => $subQueryVal,
            'num_contratti' => $subQueryContr,
        ]);

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
                    'valutazione_media' => [
                        'asc' => ['valutazione_media' => SORT_ASC],
                        'desc' => ['valutazione_media' => SORT_DESC],
                        'label' => 'Valutazione',
                    ],
                    'num_contratti' => [
                        'asc' => ['num_contratti' => SORT_ASC],
                        'desc' => ['num_contratti' => SORT_DESC],
                        'label' => 'Contratti',
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

        // Filtri esatti
        $query->andFilterWhere([
            'docente.provincia' => $this->provincia,
            'docente.provenienza' => $this->provenienza,
        ]);

        // Filtri con LIKE
        $query->andFilterWhere(['like', 'codice_fiscale', $this->codice_fiscale])
            ->andFilterWhere(['like', 'cognome', $this->cognome])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'citta', $this->citta])
            ->andFilterWhere(['like', 'titolo_studio', $this->titolo_studio]);

        // Ricerca full-text (cerca in piu campi)
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

        // Filtro per esito CTS (long list: idoneo o assegnato)
        if (!empty($this->esito_cts)) {
            $query->innerJoin('istanza i', 'i.docente_codice_fiscale = docente.codice_fiscale')
                ->andWhere(['i.esito_cts' => $this->esito_cts]);
            $query->distinct();
        }

        // Filtro per numero minimo contratti (legacy, dai filtri avanzati)
        if ($this->min_contratti !== null && $this->min_contratti > 0) {
            $query->andHaving(['>=', new Expression('num_contratti'), $this->min_contratti]);
        }

        // Filtro per valutazione minima (legacy, dai filtri avanzati)
        if ($this->min_valutazione !== null && $this->min_valutazione > 0) {
            $query->andHaving(['>=', new Expression('valutazione_media'), $this->min_valutazione]);
        }

        // Filtro colonna griglia: num_contratti
        if ($this->num_contratti !== null && $this->num_contratti !== '') {
            $query->andHaving(['num_contratti' => $this->num_contratti]);
        }

        // Filtro colonna griglia: valutazione_media
        if ($this->valutazione_media !== null && $this->valutazione_media !== '') {
            $query->andHaving(['>=', new Expression('valutazione_media'), $this->valutazione_media]);
        }

        return $dataProvider;
    }

    /**
     * Cerca docenti nella long list (idonei o assegnati)
     * per un determinato tipo di modulo/competenza
     *
     * @param array $params Parametri di ricerca
     * @param int|null $moduloId Filtra per modulo specifico
     * @return ActiveDataProvider
     */
    public function searchLongList($params, $moduloId = null)
    {
        $query = Docente::find()
            ->innerJoin('istanza i', 'i.docente_codice_fiscale = docente.codice_fiscale')
            ->where(['i.esito_cts' => [Istanza::ESITO_CTS_ASSEGNATO, Istanza::ESITO_CTS_IDONEO]])
            ->with(['assegnazioni', 'valutazioni']);

        // Filtra per modulo specifico se richiesto
        if ($moduloId) {
            $query->andWhere(['i.modulo_id' => $moduloId]);
        }

        // Aggiungi conteggio contratti come subquery
        $query->addSelect([
            'docente.*',
            'num_contratti' => new Expression('(SELECT COUNT(*) FROM assegnazione a
                INNER JOIN contratto c ON c.assegnazione_id = a.id
                WHERE a.docente_codice_fiscale = docente.codice_fiscale)'),
        ]);

        $query->distinct();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['num_contratti' => SORT_ASC, 'cognome' => SORT_ASC],
                'attributes' => [
                    'cognome',
                    'nome',
                    'provenienza',
                    'citta',
                    'num_contratti' => [
                        'asc' => ['num_contratti' => SORT_ASC],
                        'desc' => ['num_contratti' => SORT_DESC],
                        'label' => 'N. Contratti',
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Applica filtri aggiuntivi
        $query->andFilterWhere(['docente.provenienza' => $this->provenienza])
            ->andFilterWhere(['like', 'cognome', $this->cognome])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'citta', $this->citta]);

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
