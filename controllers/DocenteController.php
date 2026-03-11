<?php

namespace app\controllers;

use Yii;
use app\models\Docente;
use app\models\DocenteSearch;
use app\models\DocenteTag;
use app\models\TagCurriculum;
use app\models\TagCategoria;
use app\models\Progetto;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Istanza;
use app\models\Assegnazione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * DocenteController implementa le azioni CRUD per Docente.
 */
class DocenteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'add-tag' => ['POST'],
                    'remove-tag' => ['POST'],
                    'gestisci-tag' => ['POST'],
                    'assegna-da-albo' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutti i docenti.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocenteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Carica le categorie e i tag per i filtri
        $categorie = TagCategoria::getList();
        $tags = TagCurriculum::getListByCategory();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categorie' => $categorie,
            'tags' => $tags,
        ]);
    }

    /**
     * Visualizza un singolo docente con scheda dettagliata.
     * @param string $id Codice fiscale
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Carica tag raggruppati per categoria
        $tagsByCategoria = $model->getTagsByCategoria();

        // Carica tag disponibili per il form di aggiunta
        $tagsDisponibili = TagCurriculum::getListByCategory();

        return $this->render('view', [
            'model' => $model,
            'tagsByCategoria' => $tagsByCategoria,
            'tagsDisponibili' => $tagsDisponibili,
        ]);
    }

    /**
     * Crea un nuovo docente.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Docente();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Docente creato con successo.');
            return $this->redirect(['view', 'id' => $model->codice_fiscale]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica un docente esistente.
     * @param string $id Codice fiscale
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Docente aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->codice_fiscale]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina (soft delete) un docente.
     * @param string $id Codice fiscale
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Docente eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Aggiunge un tag a un docente.
     * @return \yii\web\Response
     */
    public function actionAddTag()
    {
        $docenteCf = Yii::$app->request->post('docente_codice_fiscale');
        $tagId = Yii::$app->request->post('tag_id');
        $livello = Yii::$app->request->post('livello');

        if ($docenteCf && $tagId) {
            $existing = DocenteTag::findOne([
                'docente_codice_fiscale' => $docenteCf,
                'tag_id' => $tagId,
            ]);

            if (!$existing) {
                $docenteTag = new DocenteTag();
                $docenteTag->docente_codice_fiscale = $docenteCf;
                $docenteTag->tag_id = $tagId;
                $docenteTag->livello = $livello ?: null;
                $docenteTag->created_by = Yii::$app->user->id;

                if ($docenteTag->save()) {
                    Yii::$app->session->setFlash('success', 'Tag aggiunto con successo.');
                } else {
                    Yii::$app->session->setFlash('error', 'Errore durante l\'aggiunta del tag.');
                }
            } else {
                Yii::$app->session->setFlash('warning', 'Questo tag è già associato al docente.');
            }
        }

        return $this->redirect(['view', 'id' => $docenteCf]);
    }

    /**
     * Rimuove un tag da un docente.
     * @return \yii\web\Response
     */
    public function actionRemoveTag()
    {
        $docenteCf = Yii::$app->request->post('docente_codice_fiscale');
        $tagId = Yii::$app->request->post('tag_id');

        if ($docenteCf && $tagId) {
            $docenteTag = DocenteTag::findOne([
                'docente_codice_fiscale' => $docenteCf,
                'tag_id' => $tagId,
            ]);

            if ($docenteTag && $docenteTag->delete()) {
                Yii::$app->session->setFlash('success', 'Tag rimosso con successo.');
            }
        }

        return $this->redirect(['view', 'id' => $docenteCf]);
    }

    /**
     * Mostra docenti con lo stesso tag.
     * @param int $tagId
     * @return string
     */
    public function actionByTag($tagId)
    {
        $tag = TagCurriculum::findOne($tagId);
        if (!$tag) {
            throw new NotFoundHttpException('Tag non trovato.');
        }

        $searchModel = new DocenteSearch();
        $dataProvider = $searchModel->searchByTag($tagId);

        return $this->render('by-tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Verifica il codice fiscale di un docente via AJAX.
     * @param string $id Codice fiscale
     * @return array
     */
    public function actionValidaCf($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $valido = Docente::verificaCodiceFiscale($model->codice_fiscale);
        $model->codice_fiscale_validato = $valido;
        $model->save(false);
        return ['success' => true, 'valido' => $valido];
    }

    /**
     * Gestisce i tag di un docente via AJAX (bulk update).
     * @param string $id Codice fiscale
     * @return array
     */
    public function actionGestisciTag($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        $tags = Yii::$app->request->post('tags', []);

        // Elimina vecchi tag
        DocenteTag::deleteAll(['docente_codice_fiscale' => $model->codice_fiscale]);

        // Crea nuovi tag
        $count = 0;
        foreach ($tags as $tagData) {
            $tagId = $tagData['tag_id'] ?? null;
            $livello = $tagData['livello'] ?? null;
            if ($tagId) {
                $docenteTag = new DocenteTag();
                $docenteTag->docente_codice_fiscale = $model->codice_fiscale;
                $docenteTag->tag_id = $tagId;
                $docenteTag->livello = $livello ?: null;
                $docenteTag->created_by = Yii::$app->user->id;
                if ($docenteTag->save()) {
                    $count++;
                }
            }
        }

        return ['success' => true, 'count' => $count];
    }

    /**
     * Pagina Albo Docenti con filtri avanzati e statistiche.
     * @return string
     */
    public function actionAlbo()
    {
        $searchModel = new DocenteSearch();
        $dataProvider = $searchModel->searchAlbo(Yii::$app->request->queryParams);

        // Statistiche per stat cards
        $totaleDocenti = Docente::find()->count();
        $totaleCoordinatori = Docente::find()->where(['tipo' => Docente::TIPO_COORDINATORE])->count();
        $totaleTutor = Docente::find()->where(['tipo' => Docente::TIPO_TUTOR])->count();

        // Docenti con assegnazioni attive (non revocate, con deleted_at null)
        $conAssegnazioniAttive = (int) Docente::find()
            ->alias('d')
            ->innerJoin('assegnazione a', 'a.docente_codice_fiscale = d.codice_fiscale AND a.deleted_at IS NULL')
            ->andWhere(['!=', 'a.status', Assegnazione::STATUS_REVOCATO])
            ->count('DISTINCT d.codice_fiscale');

        // Progetti per il modal di assegnazione
        $progetti = Progetto::find()
            ->select(['id', 'codice_progetto', 'titolo'])
            ->where(['!=', 'status', Progetto::STATUS_CONCLUSO])
            ->orderBy(['titolo' => SORT_ASC])
            ->all();

        return $this->render('albo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totaleDocenti' => $totaleDocenti,
            'totaleCoordinatori' => $totaleCoordinatori,
            'totaleTutor' => $totaleTutor,
            'conAssegnazioniAttive' => $conAssegnazioniAttive,
            'progetti' => $progetti,
        ]);
    }

    /**
     * Assegna un docente a un modulo direttamente dall'albo.
     * Crea Istanza (esito_cts=assegnato, status=accettata) + Assegnazione (status=assegnato).
     * @return Response
     */
    public function actionAssegnaDaAlbo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $docenteCf = Yii::$app->request->post('docente_codice_fiscale');
        $moduloId = Yii::$app->request->post('modulo_id');
        $note = Yii::$app->request->post('note', '');

        if (!$docenteCf || !$moduloId) {
            return ['success' => false, 'message' => 'Docente e modulo sono obbligatori.'];
        }

        $docente = Docente::findOne($docenteCf);
        if (!$docente) {
            return ['success' => false, 'message' => 'Docente non trovato.'];
        }

        $modulo = Modulo::findOne($moduloId);
        if (!$modulo) {
            return ['success' => false, 'message' => 'Modulo non trovato.'];
        }

        // Verifica che il modulo non abbia gia un docente assegnato attivo
        $assegnazioneEsistente = Assegnazione::find()
            ->where([
                'modulo_id' => $moduloId,
                'deleted_at' => null,
            ])
            ->andWhere(['!=', 'status', Assegnazione::STATUS_REVOCATO])
            ->one();

        if ($assegnazioneEsistente) {
            return ['success' => false, 'message' => 'Questo modulo ha gia un docente assegnato.'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Crea istanza
            $istanza = new Istanza();
            $istanza->avviso_id = $modulo->avviso_id;
            $istanza->docente_codice_fiscale = $docenteCf;
            $istanza->modulo_id = $moduloId;
            $istanza->data_presentazione = date('Y-m-d');
            $istanza->status = Istanza::STATUS_ACCETTATA;
            $istanza->esito_cts = Istanza::ESITO_CTS_ASSEGNATO;
            $istanza->note = $note ? 'Assegnazione diretta da Albo Docenti. ' . $note : 'Assegnazione diretta da Albo Docenti.';
            if (!$istanza->save()) {
                throw new \Exception('Errore creazione istanza: ' . implode(', ', $istanza->getFirstErrors()));
            }

            // Crea assegnazione
            $assegnazione = new Assegnazione();
            $assegnazione->modulo_id = $moduloId;
            $assegnazione->docente_codice_fiscale = $docenteCf;
            $assegnazione->istanza_id = $istanza->id;
            $assegnazione->data_assegnazione = date('Y-m-d');
            $assegnazione->status = Assegnazione::STATUS_ASSEGNATO;
            $assegnazione->note = $note ? 'Assegnazione diretta da Albo Docenti. ' . $note : 'Assegnazione diretta da Albo Docenti.';
            if (!$assegnazione->save()) {
                throw new \Exception('Errore creazione assegnazione: ' . implode(', ', $assegnazione->getFirstErrors()));
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Docente ' . $docente->getNomeCompleto() . ' assegnato con successo al modulo ' . $modulo->codice . ' - ' . $modulo->titolo,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Restituisce gli avvisi di un progetto in formato JSON.
     * @param int $progetto_id
     * @return array
     */
    public function actionAvvisiProgetto($progetto_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $avvisi = Avviso::find()
            ->where(['progetto_id' => $progetto_id])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['codice_avviso' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($avvisi as $avviso) {
            $result[] = [
                'id' => $avviso->id,
                'codice' => $avviso->codice_avviso,
                'titolo' => $avviso->titolo,
                'status' => $avviso->status,
            ];
        }

        return $result;
    }

    /**
     * Restituisce i moduli disponibili (senza assegnazione attiva) di un avviso in formato JSON.
     * @param int $avviso_id
     * @return array
     */
    public function actionModuliDisponibili($avviso_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $moduli = Modulo::find()
            ->alias('m')
            ->where(['m.avviso_id' => $avviso_id])
            ->andWhere(['m.deleted_at' => null])
            ->orderBy(['m.codice' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($moduli as $modulo) {
            // Verifica se il modulo ha gia un'assegnazione attiva
            $assegnazioneAttiva = Assegnazione::find()
                ->where([
                    'modulo_id' => $modulo->id,
                    'deleted_at' => null,
                ])
                ->andWhere(['!=', 'status', Assegnazione::STATUS_REVOCATO])
                ->one();

            $result[] = [
                'id' => $modulo->id,
                'codice' => $modulo->codice,
                'titolo' => $modulo->titolo,
                'ore_totali' => $modulo->ore_totali,
                'compenso_orario' => $modulo->compenso_orario,
                'disponibile' => $assegnazioneAttiva === null,
                'docente_assegnato' => $assegnazioneAttiva ? $assegnazioneAttiva->docente->getNomeCompleto() : null,
            ];
        }

        return $result;
    }

    /**
     * Trova il model in base al codice fiscale.
     * @param string $id Codice fiscale
     * @return Docente
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Docente::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il docente richiesto non esiste.');
    }
}
