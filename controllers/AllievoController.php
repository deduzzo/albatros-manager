<?php

namespace app\controllers;

use Yii;
use app\models\Allievo;
use app\models\AllievoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AllievoController implementa le azioni CRUD per Allievo.
 */
class AllievoController extends Controller
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
     * Lista tutti gli allievi.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AllievoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo allievo.
     * @param string $id Codice fiscale
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
     * Crea un nuovo allievo.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Allievo();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Allievo creato con successo.');
            return $this->redirect(['view', 'id' => $model->codice_fiscale]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica un allievo esistente.
     * @param string $id Codice fiscale
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Allievo aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->codice_fiscale]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina (soft delete) un allievo.
     * @param string $id Codice fiscale
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Allievo eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base al codice fiscale.
     * @param string $id Codice fiscale
     * @return Allievo
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Allievo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('L\'allievo richiesto non esiste.');
    }
}
