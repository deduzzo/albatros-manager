<?php

namespace app\controllers;

use Yii;
use app\models\TagCurriculum;
use app\models\TagCurriculumSearch;
use app\models\TagCategoria;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * TagCurriculumController implementa le azioni CRUD per TagCurriculum.
 */
class TagCurriculumController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lista tutti i tag curriculum.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TagCurriculumSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo tag curriculum.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuovo tag curriculum.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TagCurriculum();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica un tag curriculum esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina (soft delete) un tag curriculum.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Tag eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base alla PK.
     * @param int $id
     * @return TagCurriculum
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = TagCurriculum::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il tag richiesto non esiste.');
    }
}
