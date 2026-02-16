<?php

namespace app\controllers;

use Yii;
use app\models\Calendario;
use app\models\CalendarioSearch;
use app\models\Modulo;
use app\models\Docente;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class CalendarioController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
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
        $searchModel = new CalendarioSearch();
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
        $model = new Calendario();
        $model->status = Calendario::STATUS_PROGRAMMATA;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Lezione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'docenti' => self::getDocentiList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Lezione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'docenti' => self::getDocentiList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Lezione eliminata con successo.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Calendario::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Lezione non trovata.');
    }

    protected static function getModuliList()
    {
        return ArrayHelper::map(
            Modulo::find()->orderBy('titolo')->all(),
            'id',
            'titolo'
        );
    }

    protected static function getDocentiList()
    {
        return ArrayHelper::map(
            Docente::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome;
            }
        );
    }
}
