<?php

namespace app\controllers;

use Yii;
use app\models\Contratto;
use app\models\ContrattoSearch;
use app\models\Assegnazione;
use app\models\Configurazione;
use app\components\PdfGenerator;
use app\components\FirmaVerificator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
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
                    'genera' => ['POST'],
                    'upload-firmato' => ['POST'],
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
     * Genera il PDF del contratto.
     * Seleziona automaticamente il template in base alla fonte di finanziamento.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGenera($id)
    {
        $model = $this->findModel($id);
        $assegnazione = $model->assegnazione;

        if (!$assegnazione) {
            Yii::$app->session->setFlash('error', 'Impossibile generare il contratto: assegnazione non trovata.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $docente = $assegnazione->docente;
        $modulo = $assegnazione->modulo;
        $avviso = $modulo ? $modulo->avviso : null;
        $progetto = $avviso ? $avviso->progetto : null;

        if (!$docente || !$modulo || !$avviso || !$progetto) {
            Yii::$app->session->setFlash('error', 'Impossibile generare il contratto: dati incompleti (docente, modulo, avviso o progetto mancante).');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Carica dati fondazione dalla configurazione
        $configurazione = [
            'nome_fondazione' => Configurazione::get('nome_fondazione', 'Fondazione Albatros'),
            'indirizzo' => Configurazione::get('indirizzo', 'Viale Giostra 2, 98124 Messina'),
            'telefono' => Configurazione::get('telefono', ''),
            'email' => Configurazione::get('email', ''),
            'pec' => Configurazione::get('pec', ''),
            'partita_iva' => Configurazione::get('partita_iva', ''),
            'codice_fiscale_ente' => Configurazione::get('codice_fiscale_ente', '03110100835'),
            'sito_web' => Configurazione::get('sito_web', ''),
        ];

        // Determina il template in base alla fonte di finanziamento
        $fonteFinanziamento = $progetto->fonteFinanziamento;
        $codiceFonte = $fonteFinanziamento ? strtoupper($fonteFinanziamento->codice) : '';

        $isPNRR = strpos($codiceFonte, 'PNRR') !== false || strpos($codiceFonte, 'MIM') !== false;

        if ($model->tipo === Contratto::TIPO_COCOCO) {
            // Co.Co.Co. -> template bilaterale con articoli
            $template = $isPNRR ? 'cococo-pnrr' : 'cococo-fse';
        } else {
            // Prestazione professionale / Occasionale -> template lettera di incarico
            $template = $isPNRR ? 'pnrr' : 'fse';
        }

        $params = [
            'contratto' => $model,
            'assegnazione' => $assegnazione,
            'docente' => $docente,
            'modulo' => $modulo,
            'avviso' => $avviso,
            'progetto' => $progetto,
            'configurazione' => $configurazione,
        ];

        try {
            // Genera il PDF
            $html = $this->renderPartial('templates/' . $template, $params);
            $pdfContent = PdfGenerator::generate($html);

            // Salva il file
            $uploadDir = Yii::getAlias('@webroot') . '/uploads/contratti/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $filename = $model->id . '_' . time() . '.pdf';
            $filePath = $uploadDir . $filename;

            if (file_put_contents($filePath, $pdfContent) === false) {
                throw new \Exception('Impossibile salvare il file PDF.');
            }

            // Aggiorna il campo documento_generato_path
            $model->documento_generato_path = 'uploads/contratti/' . $filename;
            $model->save(false);

            Yii::$app->session->setFlash('success', 'Contratto PDF generato con successo.');
        } catch (\Throwable $e) {
            Yii::error('Errore generazione PDF contratto #' . $id . ': ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Errore durante la generazione del PDF: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Download del PDF del contratto generato.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        if (!$model->hasDocumentoGenerato()) {
            throw new NotFoundHttpException('Il documento del contratto non e\' stato ancora generato.');
        }

        $filePath = Yii::getAlias('@webroot') . '/' . $model->documento_generato_path;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Il file del contratto non e\' stato trovato.');
        }

        $docente = $model->assegnazione ? $model->assegnazione->docente : null;
        $nomeDocente = $docente ? $docente->cognome . '_' . $docente->nome : 'docente';
        $filename = 'Contratto_' . $model->id . '_' . $nomeDocente . '.pdf';

        return Yii::$app->response->sendFile($filePath, $filename);
    }

    /**
     * Anteprima del PDF del contratto (inline nel browser).
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAnteprima($id)
    {
        $model = $this->findModel($id);

        if (!$model->hasDocumentoGenerato()) {
            throw new NotFoundHttpException('Il documento del contratto non e\' stato ancora generato.');
        }

        $filePath = Yii::getAlias('@webroot') . '/' . $model->documento_generato_path;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Il file del contratto non e\' stato trovato.');
        }

        return Yii::$app->response->sendFile($filePath, 'Contratto_' . $model->id . '.pdf', [
            'inline' => true,
            'mimeType' => 'application/pdf',
        ]);
    }

    /**
     * Upload del contratto firmato (P7M o PDF).
     * Verifica automaticamente la firma digitale.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUploadFirmato($id)
    {
        $model = $this->findModel($id);

        $file = UploadedFile::getInstanceByName('file_firmato');

        if (!$file) {
            Yii::$app->session->setFlash('error', 'Nessun file caricato.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Verifica estensione
        $ext = strtolower($file->extension);
        if (!in_array($ext, ['p7m', 'pdf'])) {
            Yii::$app->session->setFlash('error', 'Formato file non supportato. Caricare un file .p7m o .pdf firmato.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Salva il file
        $uploadDir = Yii::getAlias('@webroot') . '/uploads/contratti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = $model->id . '_firmato_' . time() . '.' . $ext;
        $filePath = $uploadDir . $filename;

        if (!$file->saveAs($filePath)) {
            Yii::$app->session->setFlash('error', 'Errore durante il salvataggio del file.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Aggiorna il percorso del documento
        $model->documento_path = 'uploads/contratti/' . $filename;

        // Verifica la firma digitale
        $risultatoVerifica = FirmaVerificator::verifica($filePath);

        $model->firma_tipo = ($risultatoVerifica['tipo'] ?? 'sconosciuto') === 'p7m'
            ? Contratto::FIRMA_P7M
            : Contratto::FIRMA_PDF_FIRMATO;
        $model->firma_verificata = $risultatoVerifica['valida'] ? 1 : 0;
        $model->firma_data = $risultatoVerifica['data_firma'];
        $model->firma_dettagli = json_encode($risultatoVerifica, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Verifica match CF firmatario con docente
        $messaggioMatch = '';
        if ($risultatoVerifica['valida'] && !empty($risultatoVerifica['firmatario']['cf'])) {
            $docente = $model->assegnazione ? $model->assegnazione->docente : null;
            if ($docente && strtoupper($risultatoVerifica['firmatario']['cf']) === strtoupper($docente->codice_fiscale)) {
                $messaggioMatch = ' Il codice fiscale del firmatario corrisponde al docente assegnato.';
            } else {
                $messaggioMatch = ' ATTENZIONE: il codice fiscale del firmatario NON corrisponde al docente assegnato.';
            }
        }

        $model->save(false);

        if ($risultatoVerifica['valida']) {
            Yii::$app->session->setFlash('success', 'Contratto firmato caricato. Firma digitale VALIDA.' . $messaggioMatch);
        } else {
            $errori = implode('; ', $risultatoVerifica['errori'] ?? []);
            Yii::$app->session->setFlash('warning', 'Contratto firmato caricato. Verifica firma: ' . ($errori ?: 'firma non verificabile.') . $messaggioMatch);
        }

        return $this->redirect(['view', 'id' => $id]);
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
