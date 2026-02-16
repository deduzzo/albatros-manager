<?php

namespace app\controllers;

use Yii;
use app\models\Documentazione;
use app\models\DocumentazioneSearch;
use app\models\TipoDocumento;
use app\models\Docente;
use app\models\Allievo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class DocumentazioneController extends Controller
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
        $searchModel = new DocumentazioneSearch();
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
        $model = new Documentazione();
        $model->status = Documentazione::STATUS_ATTIVO;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Documento creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tipiDocumento' => self::getTipiDocumentoList(),
            'docenti' => self::getDocentiList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Documento aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'tipiDocumento' => self::getTipiDocumentoList(),
            'docenti' => self::getDocentiList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Documento eliminato con successo.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Documentazione::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Documento non trovato.');
    }

    protected static function getTipiDocumentoList()
    {
        return ArrayHelper::map(
            TipoDocumento::find()->where(['attivo' => true])->orderBy('descrizione')->all(),
            'id',
            'descrizione'
        );
    }

    protected static function getDocentiList()
    {
        return ArrayHelper::map(
            Docente::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome . ' (' . $model->codice_fiscale . ')';
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
