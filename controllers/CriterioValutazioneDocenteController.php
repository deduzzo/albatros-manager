<?php

namespace app\controllers;

use Yii;
use app\models\CriterioValutazioneDocente;
use app\models\CriterioValutazioneDocenteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CriterioValutazioneDocenteController - CRUD per criteri valutazione docenti.
 */
class CriterioValutazioneDocenteController extends Controller
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
                    'toggle-attivo' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutti i criteri di valutazione docenti.
     */
    public function actionIndex()
    {
        $searchModel = new CriterioValutazioneDocenteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Crea un nuovo criterio.
     */
    public function actionCreate()
    {
        $model = new CriterioValutazioneDocente();
        $model->scala_min = 1;
        $model->scala_max = 10;
        $model->ordinamento = 0;
        $model->attivo = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Criterio di valutazione docente creato con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Modifica un criterio esistente.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Criterio di valutazione docente aggiornato con successo.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina (soft delete) un criterio.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Criterio di valutazione docente eliminato con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Toggle attivo/disattivo per un criterio.
     */
    public function actionToggleAttivo($id)
    {
        $model = $this->findModel($id);
        $model->attivo = $model->attivo ? 0 : 1;
        $model->save(false, ['attivo', 'updated_at']);

        $stato = $model->attivo ? 'attivato' : 'disattivato';
        Yii::$app->session->setFlash('success', "Criterio \"{$model->nome}\" {$stato} con successo.");
        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base alla PK.
     */
    protected function findModel($id)
    {
        if (($model = CriterioValutazioneDocente::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Criterio di valutazione docente non trovato.');
    }
}
