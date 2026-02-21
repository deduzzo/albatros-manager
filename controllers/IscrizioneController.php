<?php

namespace app\controllers;

use Yii;
use app\models\Iscrizione;
use app\models\IscrizioneSearch;
use app\models\Modulo;
use app\models\Allievo;
use app\models\Avviso;
use app\models\CriterioValutazioneStudente;
use app\models\PunteggioIscrizione;
use app\models\StoricoIscrizione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

class IscrizioneController extends Controller
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
                    'ammetti' => ['POST'],
                    'ammetti-con-riserva' => ['POST'],
                    'rifiuta' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new IscrizioneSearch();
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
        $model = new Iscrizione();
        $model->status = Iscrizione::STATUS_ISCRITTO;
        $model->data_iscrizione = date('Y-m-d');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Iscrizione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Iscrizione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'moduli' => self::getModuliList(),
            'allievi' => self::getAllieviList(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Iscrizione eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Lista candidature per un bando specifico.
     * @param int $avviso_id
     * @return string
     */
    public function actionCandidature($avviso_id = null)
    {
        if ($avviso_id) {
            $avviso = Avviso::findOne($avviso_id);
            if (!$avviso) {
                throw new NotFoundHttpException('Bando non trovato.');
            }

            $dataProvider = new ActiveDataProvider([
                'query' => Iscrizione::find()
                    ->where(['avviso_id' => $avviso_id])
                    ->with(['allievo', 'modulo'])
                    ->orderBy(['punteggio_totale' => SORT_DESC, 'data_iscrizione' => SORT_ASC]),
                'pagination' => ['pageSize' => 50],
            ]);

            return $this->render('candidature', [
                'avviso' => $avviso,
                'dataProvider' => $dataProvider,
            ]);
        }

        // Senza avviso_id, mostra la lista bandi con candidature
        $avvisi = Avviso::find()->orderBy(['created_at' => SORT_DESC])->all();
        return $this->render('candidature', [
            'avviso' => null,
            'avvisi' => $avvisi,
            'dataProvider' => null,
        ]);
    }

    /**
     * Form per assegnare punteggi per criterio a un'iscrizione.
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionValuta($id)
    {
        $iscrizione = $this->findModel($id);

        // Recupera criteri: globali + specifici del bando
        $avvisoId = $iscrizione->avviso_id;
        if ($avvisoId) {
            $criteri = CriterioValutazioneStudente::findPerBando($avvisoId);
        } else {
            $criteri = CriterioValutazioneStudente::findGlobali()->all();
        }

        // Carica punteggi esistenti indicizzati per criterio_id
        $punteggiEsistenti = ArrayHelper::index($iscrizione->punteggi, 'criterio_id');

        if (Yii::$app->request->isPost) {
            $postPunteggi = Yii::$app->request->post('Punteggio', []);
            $postNote = Yii::$app->request->post('PunteggioNote', []);
            $errori = false;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($criteri as $criterio) {
                    $punteggio = isset($punteggiEsistenti[$criterio->id])
                        ? $punteggiEsistenti[$criterio->id]
                        : new PunteggioIscrizione([
                            'iscrizione_id' => $iscrizione->id,
                            'criterio_id' => $criterio->id,
                        ]);

                    $punteggio->punteggio = isset($postPunteggi[$criterio->id]) && $postPunteggi[$criterio->id] !== ''
                        ? (float)$postPunteggi[$criterio->id]
                        : null;
                    $punteggio->note = $postNote[$criterio->id] ?? null;

                    if (!$punteggio->save()) {
                        $errori = true;
                    }
                }

                if (!$errori) {
                    // Ricarica i punteggi e ricalcola il totale
                    $iscrizione->refresh();
                    $iscrizione->populateRelation('punteggi', PunteggioIscrizione::find()
                        ->where(['iscrizione_id' => $iscrizione->id])
                        ->with('criterio')
                        ->all());
                    $iscrizione->calcolaPunteggioTotale();
                    $iscrizione->save(false);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Punteggi salvati con successo. Punteggio totale: ' . $iscrizione->punteggio_totale);

                    if ($iscrizione->avviso_id) {
                        return $this->redirect(['candidature', 'avviso_id' => $iscrizione->avviso_id]);
                    }
                    return $this->redirect(['view', 'id' => $iscrizione->id]);
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Errore durante il salvataggio dei punteggi.');
                }
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Errore: ' . $e->getMessage());
            }
        }

        return $this->render('valuta', [
            'iscrizione' => $iscrizione,
            'criteri' => $criteri,
            'punteggiEsistenti' => $punteggiEsistenti,
        ]);
    }

    /**
     * Graduatoria studenti per un bando.
     * @param int $avviso_id
     * @return string
     */
    public function actionGraduatoriaStudenti($avviso_id)
    {
        $avviso = Avviso::findOne($avviso_id);
        if (!$avviso) {
            throw new NotFoundHttpException('Bando non trovato.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Iscrizione::find()
                ->where(['avviso_id' => $avviso_id])
                ->with(['allievo', 'modulo'])
                ->orderBy(['punteggio_totale' => SORT_DESC, 'data_iscrizione' => SORT_ASC]),
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'defaultOrder' => ['punteggio_totale' => SORT_DESC],
                'attributes' => [
                    'punteggio_totale',
                    'data_iscrizione',
                    'status',
                ],
            ],
        ]);

        return $this->render('graduatoria-studenti', [
            'avviso' => $avviso,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Ammette un'iscrizione.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionAmmetti($id)
    {
        $iscrizione = $this->findModel($id);
        $iscrizione->data_ammissione = date('Y-m-d');
        $iscrizione->cambiaStatus(Iscrizione::STATUS_AMMESSO, 'Ammesso dall\'amministrazione');

        Yii::$app->session->setFlash('success', 'Studente ammesso con successo.');

        if ($iscrizione->avviso_id) {
            return $this->redirect(['graduatoria-studenti', 'avviso_id' => $iscrizione->avviso_id]);
        }
        return $this->redirect(['view', 'id' => $iscrizione->id]);
    }

    /**
     * Ammette con riserva un'iscrizione.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionAmmettiConRiserva($id)
    {
        $iscrizione = $this->findModel($id);
        $iscrizione->data_ammissione = date('Y-m-d');
        $iscrizione->cambiaStatus(Iscrizione::STATUS_AMMESSO_CON_RISERVA, 'Ammesso con riserva dall\'amministrazione');

        Yii::$app->session->setFlash('success', 'Studente ammesso con riserva.');

        if ($iscrizione->avviso_id) {
            return $this->redirect(['graduatoria-studenti', 'avviso_id' => $iscrizione->avviso_id]);
        }
        return $this->redirect(['view', 'id' => $iscrizione->id]);
    }

    /**
     * Rifiuta un'iscrizione con motivazione.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRifiuta($id)
    {
        $iscrizione = $this->findModel($id);
        $motivazione = Yii::$app->request->post('motivo_non_ammissione', '');

        if (empty($motivazione)) {
            Yii::$app->session->setFlash('error', 'La motivazione del rifiuto e obbligatoria.');
            if ($iscrizione->avviso_id) {
                return $this->redirect(['graduatoria-studenti', 'avviso_id' => $iscrizione->avviso_id]);
            }
            return $this->redirect(['view', 'id' => $iscrizione->id]);
        }

        $iscrizione->motivo_non_ammissione = $motivazione;
        $iscrizione->cambiaStatus(Iscrizione::STATUS_NON_AMMESSO, $motivazione);

        Yii::$app->session->setFlash('success', 'Iscrizione rifiutata.');

        if ($iscrizione->avviso_id) {
            return $this->redirect(['graduatoria-studenti', 'avviso_id' => $iscrizione->avviso_id]);
        }
        return $this->redirect(['view', 'id' => $iscrizione->id]);
    }

    /**
     * Mostra lo storico cambi stato di un'iscrizione.
     * @param int $id
     * @return string
     */
    public function actionStorico($id)
    {
        $iscrizione = $this->findModel($id);
        $storico = StoricoIscrizione::find()
            ->where(['iscrizione_id' => $id])
            ->with(['createdByUser'])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        return $this->render('storico', [
            'iscrizione' => $iscrizione,
            'storico' => $storico,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Iscrizione::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Iscrizione non trovata.');
    }

    protected static function getModuliList()
    {
        return ArrayHelper::map(
            Modulo::find()->orderBy('titolo')->all(),
            'id',
            'titolo'
        );
    }

    protected static function getAllieviList()
    {
        return ArrayHelper::map(
            Allievo::find()->orderBy('cognome, nome')->all(),
            'codice_fiscale',
            function ($model) {
                return $model->cognome . ' ' . $model->nome . ' (' . $model->codice_fiscale . ')';
            }
        );
    }
}
