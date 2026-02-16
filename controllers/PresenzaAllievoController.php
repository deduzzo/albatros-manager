<?php

namespace app\controllers;

use Yii;
use app\models\PresenzaAllievo;
use app\models\PresenzaAllievoSearch;
use app\models\Calendario;
use app\models\Allievo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class PresenzaAllievoController extends Controller
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
        $searchModel = new PresenzaAllievoSearch();
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

    public function actionCreate($calendario_id = null)
    {
        $model = new PresenzaAllievo();
        $model->presente = true;
        if ($calendario_id) {
            $model->calendario_id = $calendario_id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Presenza registrata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'calendari' => self::getCalendariList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Presenza aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'calendari' => self::getCalendariList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Presenza eliminata con successo.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = PresenzaAllievo::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Presenza non trovata.');
    }

    protected static function getCalendariList()
    {
        return ArrayHelper::map(
            Calendario::find()->orderBy(['data' => SORT_DESC])->all(),
            'id',
            function ($model) {
                $moduloTitle = $model->modulo ? $model->modulo->titolo : 'N/D';
                return $model->data . ' - ' . $moduloTitle . ' (' . $model->ora_inizio . '-' . $model->ora_fine . ')';
            }
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
