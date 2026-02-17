<?php

namespace app\controllers;

use Yii;
use app\models\Docente;
use app\models\DocenteSearch;
use app\models\DocenteTag;
use app\models\TagCurriculum;
use app\models\TagCategoria;
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
