<?php

namespace app\controllers;

use Yii;
use app\models\Modulo;
use app\models\ModuloSearch;
use app\models\Avviso;
use app\models\Sede;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ModuloController implementa le azioni CRUD per Modulo (Corso).
 */
class ModuloController extends Controller
{
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

    public function actionIndex()
    {
        $searchModel = new ModuloSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Modulo();
        $model->status = Modulo::STATUS_BOZZA;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Modulo/Corso creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'avvisiList' => Avviso::getList(),
            'sediList' => Sede::getList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Modulo/Corso aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'avvisiList' => Avviso::getList(),
            'sediList' => Sede::getList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Modulo/Corso eliminato con successo.');

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Modulo::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il modulo richiesto non esiste.');
    }
}
