<?php

namespace app\controllers;

use Yii;
use app\models\MovimentoSpesa;
use app\models\MovimentoSpesaSearch;
use app\models\VoceSpesa;
use app\models\Progetto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * MovimentoSpesaController - CRUD per movimenti di spesa.
 */
class MovimentoSpesaController extends Controller
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
     * Lista tutti i movimenti di spesa.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new MovimentoSpesaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Calcola totali per la pagina corrente
        $query = clone $dataProvider->query;
        $totaleSpese = (float) (clone $query)->andWhere(['ms.tipo' => MovimentoSpesa::TIPO_SPESA])->sum('ms.importo') ?: 0;
        $totaleEntrate = (float) (clone $query)->andWhere(['ms.tipo' => MovimentoSpesa::TIPO_ENTRATA])->sum('ms.importo') ?: 0;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totaleSpese' => $totaleSpese,
            'totaleEntrate' => $totaleEntrate,
            'progetti' => Progetto::getList(),
            'vociSpesa' => VoceSpesa::getListGrouped(),
        ]);
    }

    /**
     * Visualizza un singolo movimento.
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
     * Crea un nuovo movimento di spesa.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new MovimentoSpesa();
        $model->tipo = MovimentoSpesa::TIPO_SPESA;
        $model->data_movimento = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // Gestione upload allegato
            $this->handleAllegato($model);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Movimento di spesa registrato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'progetti' => Progetto::getList(),
            'vociSpesa' => VoceSpesa::getListGrouped(),
        ]);
    }

    /**
     * Modifica un movimento di spesa.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $this->handleAllegato($model);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Movimento di spesa aggiornato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'progetti' => Progetto::getList(),
            'vociSpesa' => VoceSpesa::getListGrouped(),
        ]);
    }

    /**
     * Elimina un movimento (soft delete).
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Movimento di spesa eliminato con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Gestione upload allegato.
     * @param MovimentoSpesa $model
     */
    protected function handleAllegato($model)
    {
        $file = UploadedFile::getInstance($model, 'allegato_path');
        if ($file) {
            $dir = Yii::getAlias('@webroot/uploads/movimenti');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $fileName = uniqid('mov_') . '.' . $file->extension;
            $file->saveAs($dir . '/' . $fileName);
            $model->allegato_path = 'uploads/movimenti/' . $fileName;
        }
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return MovimentoSpesa
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = MovimentoSpesa::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Il movimento di spesa richiesto non esiste.');
    }
}
