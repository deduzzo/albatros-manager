<?php

namespace app\controllers;

use Yii;
use app\models\Istanza;
use app\models\IstanzaSearch;
use app\models\IstanzaTag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * IstanzaController gestisce le istanze/domande dei docenti.
 */
class IstanzaController extends Controller
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
                    'promuovi-tag' => ['POST'],
                    'promuovi-tutti-tag' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new IstanzaSearch();
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

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Istanza aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Istanza eliminata con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Promuove un singolo tag al profilo docente
     */
    public function actionPromuoviTag($id, $tagId)
    {
        $istanzaTag = IstanzaTag::find()
            ->where(['id' => $tagId, 'istanza_id' => $id])
            ->one();

        if (!$istanzaTag) {
            throw new NotFoundHttpException('Tag non trovato.');
        }

        if ($istanzaTag->isPromosso()) {
            Yii::$app->session->setFlash('warning', 'Questo tag è già stato promosso.');
        } elseif ($istanzaTag->promuovi()) {
            Yii::$app->session->setFlash('success', 'Tag promosso al profilo docente.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante la promozione del tag.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Promuove tutti i tag non ancora promossi al profilo docente
     */
    public function actionPromuoviTuttiTag($id)
    {
        $model = $this->findModel($id);
        $count = $model->promuoviTuttiTags();

        if ($count > 0) {
            Yii::$app->session->setFlash('success', "{$count} tag promossi al profilo docente.");
        } else {
            Yii::$app->session->setFlash('info', 'Nessun tag da promuovere.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Visualizza lo snapshot storico dell'istanza
     */
    public function actionSnapshot($id)
    {
        $model = $this->findModel($id);

        if (!$model->snapshot) {
            Yii::$app->session->setFlash('warning', 'Snapshot non disponibile per questa istanza.');
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('snapshot', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Istanza::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Istanza non trovata.');
    }
}
