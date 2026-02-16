<?php

namespace app\controllers;

use Yii;
use app\models\Assegnazione;
use app\models\AssegnazioneSearch;
use app\models\Modulo;
use app\models\Docente;
use app\models\Istanza;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * AssegnazioneController implementa le azioni CRUD per Assegnazione.
 */
class AssegnazioneController extends Controller
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
     * Lista tutte le assegnazioni.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssegnazioneSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza una singola assegnazione.
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
     * Crea una nuova assegnazione.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Assegnazione();
        $model->status = Assegnazione::STATUS_ASSEGNATO;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Assegnazione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'moduli' => $this->getModuliList(),
            'docenti' => $this->getDocentiList(),
            'istanze' => $this->getIstanzeList(),
        ]);
    }

    /**
     * Modifica un'assegnazione esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Assegnazione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'moduli' => $this->getModuliList(),
            'docenti' => $this->getDocentiList(),
            'istanze' => $this->getIstanzeList(),
        ]);
    }

    /**
     * Elimina (soft delete) un'assegnazione.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Assegnazione eliminata con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return Assegnazione
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Assegnazione::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('L\'assegnazione richiesta non esiste.');
    }

    /**
     * Restituisce la lista dei moduli per le dropdown.
     * @return array
     */
    protected function getModuliList()
    {
        return ArrayHelper::map(
            Modulo::find()->orderBy('titolo')->all(),
            'id',
            function ($model) {
                return $model->codice . ' - ' . $model->titolo;
            }
        );
    }

    /**
     * Restituisce la lista dei docenti per le dropdown.
     * @return array
     */
    protected function getDocentiList()
    {
        return ArrayHelper::map(
            Docente::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome . ' (' . $model->codice_fiscale . ')';
            }
        );
    }

    /**
     * Restituisce la lista delle istanze per le dropdown.
     * @return array
     */
    protected function getIstanzeList()
    {
        return ArrayHelper::map(
            Istanza::find()->orderBy('id DESC')->all(),
            'id',
            function ($model) {
                $docente = $model->docente;
                $docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D';
                return '#' . $model->id . ' - ' . $docenteNome;
            }
        );
    }
}
