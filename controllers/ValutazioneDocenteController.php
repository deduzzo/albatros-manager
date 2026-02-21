<?php

namespace app\controllers;

use Yii;
use app\models\ValutazioneDocente;
use app\models\ValutazioneDocenteSearch;
use app\models\ValutazioneCodice;
use app\models\Modulo;
use app\models\Docente;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class ValutazioneDocenteController extends Controller
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
        $searchModel = new ValutazioneDocenteSearch();
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
        $model = new ValutazioneDocente();
        $model->data_valutazione = date('Y-m-d');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Valutazione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'docenti' => self::getDocentiList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Valutazione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'docenti' => self::getDocentiList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Valutazione eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Genera codici di valutazione per un modulo
     */
    public function actionGeneraCodici($modulo_id)
    {
        $modulo = Modulo::findOne($modulo_id);
        if ($modulo === null) {
            throw new NotFoundHttpException('Modulo non trovato.');
        }

        $codiciGenerati = [];
        $quantita = 0;

        if (Yii::$app->request->isPost) {
            $quantita = (int) Yii::$app->request->post('quantita', 20);
            if ($quantita < 1) {
                $quantita = 1;
            }
            if ($quantita > 500) {
                $quantita = 500;
            }
            $codiciGenerati = ValutazioneCodice::generaCodici($modulo_id, $quantita);
            Yii::$app->session->setFlash('success', count($codiciGenerati) . ' codici generati con successo.');
        }

        // Codici esistenti per questo modulo
        $codiciEsistenti = ValutazioneCodice::find()
            ->where(['modulo_id' => $modulo_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('genera-codici', [
            'modulo' => $modulo,
            'codiciGenerati' => $codiciGenerati,
            'codiciEsistenti' => $codiciEsistenti,
        ]);
    }

    /**
     * Stampa codici per distribuzione (layout vuoto)
     */
    public function actionStampaCodici($modulo_id)
    {
        $modulo = Modulo::findOne($modulo_id);
        if ($modulo === null) {
            throw new NotFoundHttpException('Modulo non trovato.');
        }

        $codici = ValutazioneCodice::find()
            ->where(['modulo_id' => $modulo_id, 'usato_at' => null])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $this->layout = false;

        return $this->render('stampa-codici', [
            'modulo' => $modulo,
            'codici' => $codici,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = ValutazioneDocente::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Valutazione non trovata.');
    }

    protected static function getModuliList()
    {
        return ArrayHelper::map(
            Modulo::find()->orderBy('titolo')->all(),
            'id',
            'titolo'
        );
    }

    protected static function getDocentiList()
    {
        return ArrayHelper::map(
            Docente::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome;
            }
        );
    }
}
