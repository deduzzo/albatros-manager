<?php

namespace app\controllers;

use Yii;
use app\models\ValutazioneDocente;
use app\models\ValutazioneDocenteSearch;
use app\models\ValutazioneDocentePunteggio;
use app\models\ValutazioneCodice;
use app\models\CriterioValutazioneDocente;
use app\models\Modulo;
use app\models\Docente;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
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
                    'import-process' => ['POST'],
                    'match-docente' => ['POST'],
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
        $model = $this->findModel($id);
        $criteri = CriterioValutazioneDocente::findAttivi()->all();

        return $this->render('view', [
            'model' => $model,
            'criteri' => $criteri,
        ]);
    }

    public function actionCreate()
    {
        $model = new ValutazioneDocente();
        $model->data_valutazione = date('Y-m-d');
        $model->sorgente_tipo = ValutazioneDocente::SORGENTE_MANUALE;

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

    /**
     * Form upload CSV per import valutazioni.
     */
    public function actionImport()
    {
        return $this->render('import');
    }

    /**
     * Elabora il CSV caricato e importa le valutazioni.
     */
    public function actionImportProcess()
    {
        $uploadedFile = UploadedFile::getInstanceByName('csv_file');

        if (!$uploadedFile) {
            Yii::$app->session->setFlash('error', 'Nessun file caricato.');
            return $this->redirect(['import']);
        }

        // Verifica estensione
        if (!in_array(strtolower($uploadedFile->extension), ['csv', 'txt'])) {
            Yii::$app->session->setFlash('error', 'Il file deve essere in formato CSV.');
            return $this->redirect(['import']);
        }

        // Crea directory upload se non esiste
        $uploadDir = Yii::getAlias('@webroot/uploads/import-valutazioni');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filePath = $uploadDir . '/' . uniqid('import_') . '.csv';
        $uploadedFile->saveAs($filePath);

        try {
            $result = $this->processCSV($filePath);
        } catch (\Throwable $e) {
            Yii::error('Errore import CSV valutazioni: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Errore durante l\'elaborazione del CSV: ' . $e->getMessage());
            @unlink($filePath);
            return $this->redirect(['import']);
        }

        // Pulizia file temporaneo
        @unlink($filePath);

        return $this->render('import-result', [
            'result' => $result,
            'docenti' => self::getDocentiList(),
        ]);
    }

    /**
     * Matching manuale docente per valutazioni importate.
     */
    public function actionMatchDocente()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $valutazioneIds = Yii::$app->request->post('valutazione_ids', []);
        $docenteCf = Yii::$app->request->post('docente_cf');

        if (empty($valutazioneIds) || empty($docenteCf)) {
            return ['success' => false, 'message' => 'Parametri mancanti.'];
        }

        $docente = Docente::findOne(['codice_fiscale' => $docenteCf]);
        if (!$docente) {
            return ['success' => false, 'message' => 'Docente non trovato.'];
        }

        $updated = 0;
        foreach ($valutazioneIds as $id) {
            $val = ValutazioneDocente::findOne($id);
            if ($val) {
                $val->docente_codice_fiscale = $docenteCf;
                if ($val->save(false, ['docente_codice_fiscale', 'updated_at'])) {
                    $updated++;
                }
            }
        }

        return [
            'success' => true,
            'message' => "{$updated} valutazioni aggiornate con il docente {$docente->cognome} {$docente->nome}.",
        ];
    }

    /**
     * Processa il file CSV e importa le valutazioni.
     *
     * @param string $filePath Percorso file CSV
     * @return array Risultato dell'import
     */
    protected function processCSV($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Impossibile aprire il file CSV.');
        }

        // Leggi header
        $header = fgetcsv($handle, 0, ',', '"');
        if (!$header) {
            fclose($handle);
            throw new \Exception('Il file CSV è vuoto o non valido.');
        }

        // Pulizia BOM UTF-8
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        // Identifica colonne
        $colMap = $this->identifyColumns($header);

        $result = [
            'totale_righe' => 0,
            'importate' => 0,
            'errori' => 0,
            'docenti_matchati' => 0,
            'docenti_non_matchati' => 0,
            'docenti_non_matchati_dettaglio' => [],
            'log' => [],
        ];

        $docentiCache = [];
        $riga = 1;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
                $riga++;
                $result['totale_righe']++;

                // Skip righe vuote
                if (empty(array_filter($data))) {
                    continue;
                }

                // Estrai dati base
                $email = isset($colMap['email']) && isset($data[$colMap['email']]) ? trim($data[$colMap['email']]) : '';
                $nomeStudente = isset($colMap['nome_studente']) && isset($data[$colMap['nome_studente']]) ? trim($data[$colMap['nome_studente']]) : '';
                $nomeDocente = isset($colMap['docente']) && isset($data[$colMap['docente']]) ? trim($data[$colMap['docente']]) : '';
                $note = isset($colMap['note']) && isset($data[$colMap['note']]) ? trim($data[$colMap['note']]) : '';
                $url = isset($colMap['url']) && isset($data[$colMap['url']]) ? trim($data[$colMap['url']]) : '';
                $submissionDate = isset($colMap['submission_date']) && isset($data[$colMap['submission_date']]) ? trim($data[$colMap['submission_date']]) : '';

                if (empty($nomeDocente)) {
                    $result['errori']++;
                    $result['log'][] = "Riga {$riga}: nome docente mancante, skippata.";
                    continue;
                }

                // Match docente
                $docenteCf = null;
                if (isset($docentiCache[$nomeDocente])) {
                    $docenteCf = $docentiCache[$nomeDocente];
                } else {
                    $docenteCf = $this->matchDocente($nomeDocente);
                    $docentiCache[$nomeDocente] = $docenteCf;
                }

                // Parse data
                $dataValutazione = $this->parseSubmissionDate($submissionDate);
                if (!$dataValutazione) {
                    $dataValutazione = date('Y-m-d');
                }

                // Crea ValutazioneDocente
                $valutazione = new ValutazioneDocente();
                $valutazione->data_valutazione = $dataValutazione;
                $valutazione->studente_email = $email ?: null;
                $valutazione->studente_nome = $nomeStudente ?: null;
                $valutazione->docente_nome = $nomeDocente;
                $valutazione->sorgente_url = $url ?: null;
                $valutazione->sorgente_tipo = ValutazioneDocente::SORGENTE_IMPORT_CSV;
                $valutazione->note = $note ?: null;

                if ($docenteCf) {
                    $valutazione->docente_codice_fiscale = $docenteCf;
                    $result['docenti_matchati']++;
                } else {
                    $result['docenti_non_matchati']++;
                    if (!isset($result['docenti_non_matchati_dettaglio'][$nomeDocente])) {
                        $result['docenti_non_matchati_dettaglio'][$nomeDocente] = [
                            'nome' => $nomeDocente,
                            'count' => 0,
                            'valutazione_ids' => [],
                        ];
                    }
                    $result['docenti_non_matchati_dettaglio'][$nomeDocente]['count']++;
                }

                if (!$valutazione->save()) {
                    $result['errori']++;
                    $errori = implode(', ', array_map(fn($e) => implode(', ', $e), $valutazione->errors));
                    $result['log'][] = "Riga {$riga}: errore salvataggio - {$errori}";
                    continue;
                }

                // Traccia ID per matching manuale
                if (!$docenteCf && isset($result['docenti_non_matchati_dettaglio'][$nomeDocente])) {
                    $result['docenti_non_matchati_dettaglio'][$nomeDocente]['valutazione_ids'][] = $valutazione->id;
                }

                // Salva punteggi per criterio
                $punteggiSalvati = 0;
                foreach ($colMap['criteri'] as $criterioId => $colIndex) {
                    if (!isset($data[$colIndex])) {
                        continue;
                    }
                    $valore = trim($data[$colIndex]);
                    if ($valore === '' || !is_numeric($valore)) {
                        continue;
                    }

                    $punteggio = new ValutazioneDocentePunteggio();
                    $punteggio->valutazione_docente_id = $valutazione->id;
                    $punteggio->criterio_id = $criterioId;
                    $punteggio->punteggio = (int) $valore;

                    if ($punteggio->save()) {
                        $punteggiSalvati++;
                    }
                }

                // Calcola e salva punteggio globale (media)
                $media = $valutazione->getMediaPunteggi();
                if ($media !== null) {
                    $valutazione->punteggio_globale = round($media);
                    $valutazione->save(false, ['punteggio_globale']);
                }

                $result['importate']++;
                $result['log'][] = "Riga {$riga}: importata ({$nomeDocente}, {$punteggiSalvati} criteri)" . ($docenteCf ? '' : ' [docente non matchato]');
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        fclose($handle);

        return $result;
    }

    /**
     * Identifica le colonne nel CSV mappandole ai campi noti.
     *
     * @param array $header Array di intestazioni CSV
     * @return array Mappa colonne
     */
    protected function identifyColumns($header)
    {
        $map = [
            'email' => null,
            'nome_studente' => null,
            'docente' => null,
            'note' => null,
            'url' => null,
            'submission_date' => null,
            'timezone' => null,
            'criteri' => [],
        ];

        $criteri = CriterioValutazioneDocente::findAttivi()->all();

        foreach ($header as $index => $colName) {
            $colName = trim($colName);
            $colLower = mb_strtolower($colName);

            // Colonne fisse
            if ($colLower === 'email') {
                $map['email'] = $index;
                continue;
            }
            if (in_array($colLower, ['nome e cognome', 'nome e cognome studente', 'studente'])) {
                $map['nome_studente'] = $index;
                continue;
            }
            if (in_array($colLower, ['docente', 'nome e/o cognome - docente', 'nome docente'])) {
                $map['docente'] = $index;
                continue;
            }
            if (in_array($colLower, ['note', 'nota', 'commento'])) {
                $map['note'] = $index;
                continue;
            }
            if ($colLower === 'url') {
                $map['url'] = $index;
                continue;
            }
            if (in_array($colLower, ['submission date', 'data', 'data invio'])) {
                $map['submission_date'] = $index;
                continue;
            }
            if (in_array($colLower, ['timezone', 'fuso orario'])) {
                $map['timezone'] = $index;
                continue;
            }

            // Prova match con criteri (per descrizione)
            $criterio = CriterioValutazioneDocente::findByDescrizione($colName);
            if ($criterio) {
                $map['criteri'][$criterio->id] = $index;
            }
        }

        return $map;
    }

    /**
     * Match fuzzy del nome docente con i docenti in DB.
     *
     * @param string $nomeDocente Nome dal CSV (es. "Buscema Flavia")
     * @return string|null Codice fiscale del docente matchato, oppure null
     */
    protected function matchDocente($nomeDocente)
    {
        $nomeDocente = trim($nomeDocente);
        if (empty($nomeDocente)) {
            return null;
        }

        // Parsing: "Cognome Nome" o "Nome Cognome"
        $parti = preg_split('/\s+/', $nomeDocente);
        if (count($parti) < 2) {
            // Cerca solo per cognome
            $docente = Docente::find()
                ->where(['like', 'cognome', $parti[0]])
                ->one();
            if ($docente) {
                return $docente->codice_fiscale;
            }
            return null;
        }

        // Prova: prima parola = cognome, resto = nome
        $cognome1 = $parti[0];
        $nome1 = implode(' ', array_slice($parti, 1));

        // Prova: ultima parola = cognome (o viceversa)
        $cognome2 = end($parti);
        $nome2 = implode(' ', array_slice($parti, 0, -1));

        // Match esatto cognome+nome
        $docente = Docente::find()
            ->where(['like', 'cognome', $cognome1])
            ->andWhere(['like', 'nome', $nome1])
            ->one();

        if ($docente) {
            return $docente->codice_fiscale;
        }

        // Prova inverso
        $docente = Docente::find()
            ->where(['like', 'cognome', $cognome2])
            ->andWhere(['like', 'nome', $nome2])
            ->one();

        if ($docente) {
            return $docente->codice_fiscale;
        }

        // Prova con cognome composto (es. "Cavallaro Vincenzo Giuseppe")
        if (count($parti) > 2) {
            // Cognome = prima parola, Nome = tutto il resto
            $docente = Docente::find()
                ->where(['like', 'cognome', $parti[0]])
                ->andWhere(['like', 'nome', implode(' ', array_slice($parti, 1))])
                ->one();
            if ($docente) {
                return $docente->codice_fiscale;
            }
        }

        // Ultimo tentativo: match solo cognome se unico risultato
        $risultati = Docente::find()
            ->where(['like', 'cognome', $cognome1])
            ->all();

        if (count($risultati) === 1) {
            return $risultati[0]->codice_fiscale;
        }

        return null;
    }

    /**
     * Parsa la data dal formato CSV (es. "Dec 21st 2025, 4:26 pm").
     *
     * @param string $dateStr
     * @return string|null Data in formato Y-m-d o null
     */
    protected function parseSubmissionDate($dateStr)
    {
        if (empty($dateStr)) {
            return null;
        }

        // Rimuovi suffissi ordinali (st, nd, rd, th)
        $cleaned = preg_replace('/(\d+)(st|nd|rd|th)/i', '$1', $dateStr);

        // Prova parse
        $timestamp = strtotime($cleaned);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
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
