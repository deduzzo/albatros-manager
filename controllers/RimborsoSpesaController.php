<?php

namespace app\controllers;

use Yii;
use app\models\RimborsoSpesa;
use app\models\RimborsoSpesaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * RimborsoSpesaController implementa le azioni CRUD per RimborsoSpesa (Admin).
 */
class RimborsoSpesaController extends Controller
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
                    'approva' => ['POST'],
                    'rifiuta' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutti i rimborsi spesa.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RimborsoSpesaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo rimborso spesa.
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
     * Approva un rimborso spesa.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionApprova($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RimborsoSpesa::STATUS_RICHIESTO) {
            Yii::$app->session->setFlash('error', 'Solo i rimborsi in stato "Richiesto" possono essere approvati.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $userId = Yii::$app->user->id;
        if ($model->approva($userId)) {
            Yii::$app->session->setFlash('success', 'Rimborso spesa approvato con successo.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante l\'approvazione del rimborso.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Rifiuta un rimborso spesa con motivo obbligatorio.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRifiuta($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RimborsoSpesa::STATUS_RICHIESTO) {
            Yii::$app->session->setFlash('error', 'Solo i rimborsi in stato "Richiesto" possono essere rifiutati.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $motivo = Yii::$app->request->post('motivo_rifiuto');
        if (empty($motivo)) {
            Yii::$app->session->setFlash('error', 'Il motivo del rifiuto e\' obbligatorio.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $userId = Yii::$app->user->id;
        if ($model->rifiuta($motivo, $userId)) {
            Yii::$app->session->setFlash('success', 'Rimborso spesa rifiutato.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante il rifiuto del rimborso.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Elimina (soft delete) un rimborso spesa.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Rimborso spesa eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return RimborsoSpesa
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = RimborsoSpesa::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il rimborso spesa richiesto non esiste.');
    }
}
