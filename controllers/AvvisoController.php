<?php

namespace app\controllers;

use Yii;
use app\models\Avviso;
use app\models\AvvisoSearch;
use app\models\Progetto;
use app\models\AvvisoDocumentoTipo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * AvvisoController implementa le azioni CRUD per Avviso (Bando).
 */
class AvvisoController extends Controller
{
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
                    'delete-documento' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AvvisoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Avviso();
        $model->status = Avviso::STATUS_BOZZA;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Avviso/Bando creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'progettiList' => Progetto::getList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Avviso/Bando aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'progettiList' => Progetto::getList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Avviso/Bando eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Gestisce i documenti richiesti per un avviso
     */
    public function actionDocumenti($id)
    {
        $avviso = $this->findModel($id);
        $documenti = $avviso->documentiRichiesti;

        $nuovoDocumento = new AvvisoDocumentoTipo();
        $nuovoDocumento->avviso_id = $id;

        if ($nuovoDocumento->load(Yii::$app->request->post()) && $nuovoDocumento->save()) {
            Yii::$app->session->setFlash('success', 'Documento richiesto aggiunto con successo.');
            return $this->redirect(['documenti', 'id' => $id]);
        }

        return $this->render('documenti', [
            'avviso' => $avviso,
            'documenti' => $documenti,
            'nuovoDocumento' => $nuovoDocumento,
        ]);
    }

    /**
     * Elimina un documento richiesto
     */
    public function actionDeleteDocumento($id)
    {
        $documento = AvvisoDocumentoTipo::findOne($id);
        if ($documento === null) {
            throw new NotFoundHttpException('Documento non trovato.');
        }

        $avvisoId = $documento->avviso_id;
        $documento->delete();
        Yii::$app->session->setFlash('success', 'Documento richiesto eliminato.');

        return $this->redirect(['documenti', 'id' => $avvisoId]);
    }

    protected function findModel($id)
    {
        if (($model = Avviso::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('L\'avviso richiesto non esiste.');
    }
}
