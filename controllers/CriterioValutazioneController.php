<?php

namespace app\controllers;

use Yii;
use app\models\CriterioValutazioneStudente;
use app\models\CriterioValutazioneStudenteSearch;
use app\models\Avviso;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * CriterioValutazioneController - CRUD per CriterioValutazioneStudente.
 */
class CriterioValutazioneController extends Controller
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

    /**
     * Lista tutti i criteri di valutazione.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CriterioValutazioneStudenteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo criterio.
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuovo criterio di valutazione.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new CriterioValutazioneStudente();
        $model->peso = 1;
        $model->punteggio_min = 0;
        $model->punteggio_max = 10;
        $model->ordine = 0;
        $model->attivo = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Criterio di valutazione creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'avvisi' => self::getAvvisiList(),
        ]);
    }

    /**
     * Modifica un criterio esistente.
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Criterio di valutazione aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'avvisi' => self::getAvvisiList(),
        ]);
    }

    /**
     * Elimina (soft delete) un criterio.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Criterio di valutazione eliminato con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base alla PK.
     * @param int $id
     * @return CriterioValutazioneStudente
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = CriterioValutazioneStudente::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Criterio di valutazione non trovato.');
    }

    /**
     * Restituisce la lista avvisi per dropdown.
     * @return array
     */
    protected static function getAvvisiList()
    {
        return Avviso::getList();
    }
}
