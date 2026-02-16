<?php

namespace app\controllers;

use Yii;
use app\models\Iscrizione;
use app\models\IscrizioneSearch;
use app\models\Modulo;
use app\models\Allievo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class IscrizioneController extends Controller
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
        $searchModel = new IscrizioneSearch();
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
        $model = new Iscrizione();
        $model->status = Iscrizione::STATUS_ISCRITTO;
        $model->data_iscrizione = date('Y-m-d');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Iscrizione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Iscrizione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Iscrizione eliminata con successo.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Iscrizione::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Iscrizione non trovata.');
    }

    protected static function getModuliList()
    {
        return ArrayHelper::map(
            Modulo::find()->orderBy('titolo')->all(),
            'id',
            'titolo'
        );
    }

    protected static function getAllieviList()
    {
        return ArrayHelper::map(
            Allievo::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome . ' (' . $model->codice_fiscale . ')';
            }
        );
    }
}
