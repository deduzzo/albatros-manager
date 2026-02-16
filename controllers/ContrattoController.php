<?php

namespace app\controllers;

use Yii;
use app\models\Contratto;
use app\models\ContrattoSearch;
use app\models\Assegnazione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * ContrattoController implementa le azioni CRUD per Contratto.
 */
class ContrattoController extends Controller
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
     * Lista tutti i contratti.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ContrattoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo contratto.
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
     * Crea un nuovo contratto.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Contratto();
        $model->status = Contratto::STATUS_BOZZA;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Contratto creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'assegnazioni' => $this->getAssegnazioniList(),
        ]);
    }

    /**
     * Modifica un contratto esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Contratto aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'assegnazioni' => $this->getAssegnazioniList(),
        ]);
    }

    /**
     * Elimina (soft delete) un contratto.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Contratto eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return Contratto
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Contratto::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il contratto richiesto non esiste.');
    }

    /**
     * Restituisce la lista delle assegnazioni per le dropdown.
     * @return array
     */
    protected function getAssegnazioniList()
    {
        return ArrayHelper::map(
            Assegnazione::find()
                ->joinWith(['docente', 'modulo'])
                ->orderBy('id DESC')
                ->all(),
            'id',
            function ($model) {
                $docente = $model->docente ? $model->docente->cognome . ' ' . $model->docente->nome : 'N/D';
                $modulo = $model->modulo ? $model->modulo->codice : 'N/D';
                return '#' . $model->id . ' - ' . $docente . ' (' . $modulo . ')';
            }
        );
    }
}
