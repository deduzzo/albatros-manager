<?php

namespace app\controllers;

use Yii;
use app\models\Assegnazione;
use app\models\AssegnazioneSearch;
use app\models\Modulo;
use app\models\Docente;
use app\models\Istanza;
use app\models\StoricoEsito;
use app\models\Contratto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
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
                    'revoca' => ['POST'],
                    'conferma-subentro' => ['POST'],
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
     * Revoca un'assegnazione (POST).
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRevoca($id)
    {
        $model = $this->findModel($id);

        if (!$model->isRevocabile()) {
            Yii::$app->session->setFlash('error', 'Questa assegnazione non può essere revocata (stato: ' . $model->getStatusLabel() . ').');
            return $this->redirect(['view', 'id' => $id]);
        }

        $note = Yii::$app->request->post('note_revoca', '');
        $tipo = Yii::$app->request->post('tipo_revoca', StoricoEsito::ESITO_RINUNCIA);

        if ($model->revoca($note, $tipo)) {
            Yii::$app->session->setFlash('success', 'Assegnazione revocata con successo.');

            // Controlla se ci sono idonei disponibili per subentro
            $modulo = $model->modulo;
            if ($modulo && $modulo->getIdoneiDisponibiliCount() > 0) {
                Yii::$app->session->setFlash('info', 'Ci sono ' . $modulo->getIdoneiDisponibiliCount() . ' docenti idonei disponibili per il subentro.');
                return $this->redirect(['subentro', 'id' => $id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante la revoca dell\'assegnazione.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Mostra la pagina di subentro con la lista dei docenti idonei disponibili.
     * @param int $id ID dell'assegnazione revocata
     * @return string
     */
    public function actionSubentro($id)
    {
        $model = $this->findModel($id);
        $modulo = $model->modulo;

        if (!$modulo) {
            throw new NotFoundHttpException('Modulo non trovato per questa assegnazione.');
        }

        // Lista idonei disponibili per il modulo
        $idoneiDisponibili = $modulo->getIstanzeIdonee();

        // Graduatoria completa per contesto
        $graduatoria = $modulo->getGraduatoria();

        return $this->render('subentro', [
            'assegnazioneRevocata' => $model,
            'modulo' => $modulo,
            'idoneiDisponibili' => $idoneiDisponibili,
            'graduatoria' => $graduatoria,
        ]);
    }

    /**
     * Conferma il subentro: crea nuova assegnazione per il docente idoneo selezionato (POST).
     * @param int $id ID dell'assegnazione revocata
     * @return \yii\web\Response
     */
    public function actionConfermaSubentro($id)
    {
        $assegnazioneRevocata = $this->findModel($id);
        $istanzaId = Yii::$app->request->post('istanza_id');
        $note = Yii::$app->request->post('note_subentro', '');

        if (!$istanzaId) {
            Yii::$app->session->setFlash('error', 'Selezionare un docente idoneo per il subentro.');
            return $this->redirect(['subentro', 'id' => $id]);
        }

        $istanzaIdoneo = Istanza::findOne($istanzaId);
        if (!$istanzaIdoneo || !$istanzaIdoneo->isIdoneo()) {
            Yii::$app->session->setFlash('error', 'L\'istanza selezionata non è valida per il subentro.');
            return $this->redirect(['subentro', 'id' => $id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Registra esito "subentro" sull'istanza dell'idoneo
            $storico = new StoricoEsito();
            $storico->istanza_id = $istanzaIdoneo->id;
            $storico->esito = StoricoEsito::ESITO_SUBENTRO;
            $storico->data_esito = date('Y-m-d');
            $storico->note = 'Subentro per sostituzione di ' .
                ($assegnazioneRevocata->docente ? $assegnazioneRevocata->docente->getNomeCompleto() : 'docente revocato') .
                ($note ? '. ' . $note : '');
            if (!$storico->save()) {
                throw new \Exception('Errore nel salvare lo storico esito di subentro.');
            }

            // 2. Aggiorna esito_cts dell'istanza idoneo a "assegnato"
            $istanzaIdoneo->esito_cts = Istanza::ESITO_CTS_ASSEGNATO;
            if (!$istanzaIdoneo->save(false)) {
                throw new \Exception('Errore nell\'aggiornare l\'esito CTS dell\'istanza.');
            }

            // 3. Crea nuova assegnazione
            $nuovaAssegnazione = new Assegnazione();
            $nuovaAssegnazione->modulo_id = $assegnazioneRevocata->modulo_id;
            $nuovaAssegnazione->docente_codice_fiscale = $istanzaIdoneo->docente_codice_fiscale;
            $nuovaAssegnazione->istanza_id = $istanzaIdoneo->id;
            $nuovaAssegnazione->data_assegnazione = date('Y-m-d');
            $nuovaAssegnazione->delibera_cts = $assegnazioneRevocata->delibera_cts;
            $nuovaAssegnazione->status = Assegnazione::STATUS_ASSEGNATO;
            $nuovaAssegnazione->note = 'Subentro per assegnazione #' . $assegnazioneRevocata->id .
                ($note ? '. ' . $note : '');

            if (!$nuovaAssegnazione->save()) {
                throw new \Exception('Errore nella creazione della nuova assegnazione: ' . implode(', ', $nuovaAssegnazione->getFirstErrors()));
            }

            $transaction->commit();

            $docenteNome = $istanzaIdoneo->docente ? $istanzaIdoneo->docente->getNomeCompleto() : $istanzaIdoneo->docente_codice_fiscale;
            Yii::$app->session->setFlash('success', 'Subentro completato! ' . $docenteNome . ' è stato assegnato al modulo.');
            return $this->redirect(['view', 'id' => $nuovaAssegnazione->id]);

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Errore subentro: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Errore durante il subentro: ' . $e->getMessage());
            return $this->redirect(['subentro', 'id' => $id]);
        }
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
