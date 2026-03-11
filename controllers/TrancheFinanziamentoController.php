<?php

namespace app\controllers;

use Yii;
use app\models\TrancheFinanziamento;
use app\models\TrancheFinanziamentoSearch;
use app\models\Progetto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * TrancheFinanziamentoController - CRUD per tranche di finanziamento.
 */
class TrancheFinanziamentoController extends Controller
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
     * Lista tutte le tranche di finanziamento.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TrancheFinanziamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'progetti' => Progetto::getList(),
        ]);
    }

    /**
     * Visualizza una singola tranche.
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
     * Crea una nuova tranche.
     * @param int|null $progetto_id Pre-seleziona il progetto
     * @return string|\yii\web\Response
     */
    public function actionCreate($progetto_id = null)
    {
        $model = new TrancheFinanziamento();
        $model->status = TrancheFinanziamento::STATUS_PREVISTA;

        if ($progetto_id) {
            $model->progetto_id = $progetto_id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tranche di finanziamento creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'progetti' => Progetto::getList(),
        ]);
    }

    /**
     * Modifica una tranche esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tranche aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'progetti' => Progetto::getList(),
        ]);
    }

    /**
     * Elimina una tranche (soft delete).
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Tranche eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return TrancheFinanziamento
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = TrancheFinanziamento::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La tranche richiesta non esiste.');
    }
}
