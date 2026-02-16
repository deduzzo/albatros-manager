<?php

namespace app\controllers;

use Yii;
use app\models\Pagamento;
use app\models\PagamentoSearch;
use app\models\Contratto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PagamentoController implementa le azioni CRUD per Pagamento.
 */
class PagamentoController extends Controller
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
     * Lista tutti i pagamenti.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PagamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo pagamento.
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
     * Crea un nuovo pagamento.
     * @param int|null $contratto_id Pre-seleziona il contratto se passato
     * @return string|\yii\web\Response
     */
    public function actionCreate($contratto_id = null)
    {
        $model = new Pagamento();
        $model->status = Pagamento::STATUS_PROGRAMMATO;
        $model->tipo = Pagamento::TIPO_ACCONTO;

        if ($contratto_id) {
            $model->contratto_id = $contratto_id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Pagamento creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
        ]);
    }

    /**
     * Modifica un pagamento esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Pagamento aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
        ]);
    }

    /**
     * Elimina (soft delete) un pagamento.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Pagamento eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return Pagamento
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Pagamento::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il pagamento richiesto non esiste.');
    }

    /**
     * Restituisce la lista dei contratti per le dropdown.
     * @return array
     */
    protected function getContrattiList()
    {
        return ArrayHelper::map(
            Contratto::find()
                ->joinWith(['assegnazione.docente'])
                ->orderBy('id DESC')
                ->all(),
            'id',
            function ($model) {
                $docente = $model->assegnazione && $model->assegnazione->docente
                    ? $model->assegnazione->docente->cognome . ' ' . $model->assegnazione->docente->nome
                    : 'N/D';
                $numero = $model->numero_contratto ?: 'N. ' . $model->id;
                return $numero . ' - ' . $docente;
            }
        );
    }
}
