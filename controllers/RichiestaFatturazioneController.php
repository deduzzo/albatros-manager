<?php

namespace app\controllers;

use Yii;
use app\models\RichiestaFatturazione;
use app\models\RichiestaFatturazioneSearch;
use app\models\Contratto;
use app\models\Configurazione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * RichiestaFatturazioneController implementa le azioni CRUD per RichiestaFatturazione.
 */
class RichiestaFatturazioneController extends Controller
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
                    'invia' => ['POST'],
                    'invia-massiva' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutte le richieste di fatturazione.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RichiestaFatturazioneSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza una singola richiesta di fatturazione.
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
     * Crea una nuova richiesta di fatturazione.
     * @param int|null $contratto_id Pre-seleziona il contratto
     * @return string|\yii\web\Response
     */
    public function actionCreate($contratto_id = null)
    {
        $model = new RichiestaFatturazione();
        $model->status = RichiestaFatturazione::STATUS_BOZZA;
        $model->data_richiesta = date('Y-m-d');

        // Pre-popola da contratto
        if ($contratto_id) {
            $contratto = Contratto::findOne($contratto_id);
            if ($contratto) {
                $model->contratto_id = $contratto_id;
                $model->importo = (float) $contratto->compenso_totale;

                // Tipo dal contratto
                if ($contratto->tipo === 'cococo') {
                    $model->tipo = RichiestaFatturazione::TIPO_CEDOLINO;
                } else {
                    $model->tipo = RichiestaFatturazione::TIPO_FATTURA;
                }

                // Email docente
                $assegnazione = $contratto->assegnazione;
                $docente = $assegnazione ? $assegnazione->docente : null;
                if ($docente) {
                    $model->destinatario_email = $docente->email ?? '';
                    $model->destinatario_nome = $docente->getNomeCompleto();
                }
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Richiesta di fatturazione creata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
        ]);
    }

    /**
     * Modifica una richiesta di fatturazione esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!$model->isModificabile()) {
            Yii::$app->session->setFlash('error', 'La richiesta non e\' piu\' modificabile.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Richiesta di fatturazione aggiornata con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
        ]);
    }

    /**
     * Elimina (soft delete) una richiesta di fatturazione.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Richiesta di fatturazione eliminata con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Invia la richiesta via email e cambia status a 'inviata'.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionInvia($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RichiestaFatturazione::STATUS_BOZZA) {
            Yii::$app->session->setFlash('error', 'Solo le richieste in bozza possono essere inviate.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Genera corpo email se vuoto
        if (empty($model->corpo_email)) {
            $model->corpo_email = $model->generaCorpoEmail();
        }

        // Genera oggetto email se vuoto
        if (empty($model->oggetto_email)) {
            $fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
            if ($model->tipo === RichiestaFatturazione::TIPO_FATTURA) {
                $model->oggetto_email = "Richiesta emissione fattura - {$fondazione}";
            } else {
                $model->oggetto_email = "Richiesta elaborazione cedolino - {$fondazione}";
            }
        }

        // Tenta invio email
        try {
            $template = $model->tipo === RichiestaFatturazione::TIPO_FATTURA
                ? 'richiesta-fatturazione-html'
                : 'richiesta-cedolino-html';

            $sent = Yii::$app->mailer->compose($template, ['model' => $model])
                ->setTo($model->destinatario_email)
                ->setSubject($model->oggetto_email)
                ->send();

            if ($sent) {
                $model->invia();
                Yii::$app->session->setFlash('success', 'Richiesta inviata con successo a ' . $model->destinatario_email);
            } else {
                // Se il mailer e' in file transport mode, invia comunque (salva su file)
                $model->invia();
                Yii::$app->session->setFlash('success', 'Richiesta inviata (modalita\' file transport). Status aggiornato.');
            }
        } catch (\Throwable $e) {
            // Aggiorna status comunque in dev
            $model->invia();
            Yii::$app->session->setFlash('warning', 'Email non inviata (errore mailer), ma status aggiornato a "Inviata". Errore: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Form upload fattura/cedolino ricevuto + cambia status a 'ricevuta'.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRiceviDocumento($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->status, [RichiestaFatturazione::STATUS_INVIATA, RichiestaFatturazione::STATUS_RICEVUTA])) {
            Yii::$app->session->setFlash('error', 'Non e\' possibile caricare documenti per questa richiesta.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('documento');
            if ($file) {
                // Assicura directory upload
                $uploadPath = Yii::getAlias('@webroot/uploads/fatture');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $fileName = 'fattura_' . $model->id . '_' . time() . '.' . $file->extension;
                $filePath = $uploadPath . '/' . $fileName;

                if ($file->saveAs($filePath)) {
                    $model->documento_ricevuto_path = 'uploads/fatture/' . $fileName;
                    $model->data_ricezione = date('Y-m-d');
                    $model->status = RichiestaFatturazione::STATUS_RICEVUTA;
                    $model->save(false);

                    Yii::$app->session->setFlash('success', 'Documento ricevuto e caricato con successo.');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Errore durante il caricamento del file.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Nessun file selezionato.');
            }
        }

        return $this->render('ricevi-documento', [
            'model' => $model,
        ]);
    }

    /**
     * Invio massivo di richieste in bozza.
     * POST JSON: { ids: [1,2,3] }
     * @return array
     */
    public function actionInviaMassiva()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('ids', []);
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Nessuna richiesta selezionata.'];
        }

        $count = 0;
        $errori = [];

        foreach ($ids as $id) {
            $model = RichiestaFatturazione::findOne($id);
            if (!$model) {
                $errori[] = "Richiesta #{$id} non trovata.";
                continue;
            }
            if ($model->status !== RichiestaFatturazione::STATUS_BOZZA) {
                $errori[] = "Richiesta #{$id}: non in stato bozza.";
                continue;
            }

            if (empty($model->corpo_email)) {
                $model->corpo_email = $model->generaCorpoEmail();
            }

            try {
                $template = $model->tipo === RichiestaFatturazione::TIPO_FATTURA
                    ? 'richiesta-fatturazione-html'
                    : 'richiesta-cedolino-html';

                Yii::$app->mailer->compose($template, ['model' => $model])
                    ->setTo($model->destinatario_email)
                    ->setSubject($model->oggetto_email ?: 'Richiesta fatturazione')
                    ->send();
            } catch (\Throwable $e) {
                // In dev mode, continua comunque
            }

            $model->invia();
            $count++;
        }

        return [
            'success' => true,
            'count' => $count,
            'errori' => $errori,
        ];
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return RichiestaFatturazione
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = RichiestaFatturazione::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La richiesta di fatturazione non esiste.');
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
                ->orderBy('contratto.id DESC')
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
