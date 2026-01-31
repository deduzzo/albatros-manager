<?php

namespace app\controllers;

use Yii;
use app\models\FonteFinanziamento;
use app\models\FonteFinanziamentoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * FonteFinanziamentoController implementa le azioni CRUD per FonteFinanziamento.
 */
class FonteFinanziamentoController extends Controller
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
     * Lista tutte le fonti di finanziamento.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new FonteFinanziamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza una singola fonte di finanziamento.
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
     * Crea una nuova fonte di finanziamento.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new FonteFinanziamento();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Fonte di finanziamento creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica una fonte di finanziamento esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Fonte di finanziamento aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina (soft delete) una fonte di finanziamento.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Fonte di finanziamento eliminata con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base alla PK.
     * @param int $id
     * @return FonteFinanziamento
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = FonteFinanziamento::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La fonte di finanziamento richiesta non esiste.');
    }
}
