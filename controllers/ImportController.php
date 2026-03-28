<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\models\Progetto;
use app\models\Avviso;
use app\models\Sede;
use app\models\Modulo;
use app\models\Docente;
use app\models\Istanza;
use app\models\Assegnazione;
use app\models\Contratto;
use app\models\Pagamento;
use app\models\FonteFinanziamento;
use app\models\Calendario;

/**
 * Controller per import dati da Excel
 */
class ImportController extends Controller
{
    public $layout = 'main';

    private $log = [];
    private $errors = [];
    private $warnings = [];

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
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'upload' => ['POST'],
                    'upload-assegnazioni' => ['POST'],
                    'upload-calendari' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Disabilita CSRF per upload (gestito via AJAX)
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['upload', 'upload-assegnazioni', 'upload-calendari'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Pagina principale import
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Upload e import file Excel
     */
    public function actionUpload()
    {
        // Forza risposta JSON per evitare HTML su errori
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        Yii::info('ImportController::actionUpload called', __METHOD__);

        $uploadedFile = UploadedFile::getInstanceByName('excel_file');

        if (!$uploadedFile) {
            Yii::error('No file uploaded', __METHOD__);
            return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        }

        Yii::info('File uploaded: ' . $uploadedFile->name, __METHOD__);

        // Salva temporaneamente
        $tempPath = Yii::getAlias('@runtime') . '/import_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) {
            return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        }

        try {
            // Carica Excel
            $spreadsheet = IOFactory::load($tempPath);

            // Leggi fogli
            $infoProgetto = $this->leggiInfoProgetto($spreadsheet);
            $moduli = $this->leggiModuli($spreadsheet);
            $docentiEsiti = $this->leggiDocentiEsiti($spreadsheet);

            // Valida dati
            if (!$this->validaDati($infoProgetto, $moduli, $docentiEsiti)) {
                @unlink($tempPath);
                return $this->asJson([
                    'success' => false,
                    'errors' => $this->errors,
                    'warnings' => $this->warnings
                ]);
            }

            // Leggi parametro overwrite
            $overwrite = Yii::$app->request->post('overwrite') === '1';

            // Esegui import in transazione
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $result = $this->eseguiImport($infoProgetto, $moduli, $docentiEsiti, false, $overwrite);

                if ($result) {
                    $transaction->commit();
                    @unlink($tempPath);

                    return $this->asJson([
                        'success' => true,
                        'log' => $this->log,
                        'warnings' => $this->warnings
                    ]);
                } else {
                    $transaction->rollBack();
                    @unlink($tempPath);

                    return $this->asJson([
                        'success' => false,
                        'errors' => $this->errors,
                        'warnings' => $this->warnings
                    ]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                @unlink($tempPath);
                throw $e;
            }

        } catch (\Throwable $e) {
            @unlink($tempPath);
            Yii::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => 'Errore durante l\'import: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Leggi foglio INFO PROGETTO
     */
    private function leggiInfoProgetto($spreadsheet)
    {
        $sheet = $spreadsheet->getSheetByName('ℹ️ INFO PROGETTO');
        if (!$sheet) {
            $this->errors[] = 'Foglio "INFO PROGETTO" non trovato';
            return null;
        }

        $data = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                // Normalizza il valore in stringa
                if (is_object($value)) {
                    $value = $value->__toString();
                } elseif (is_array($value)) {
                    $value = '';  // Array vuoto o da formula -> stringa vuota
                } elseif ($value === null) {
                    $value = '';
                } else {
                    $value = (string)$value;
                }
                $cells[] = $value;
            }

            if (!empty($cells[0])) {
                $data[$cells[0]] = $cells[1] ?? null;
            }
        }

        return $data;
    }

    /**
     * Leggi foglio MODULI
     */
    private function leggiModuli($spreadsheet)
    {
        $sheet = $spreadsheet->getSheetByName('📚 MODULI');
        if (!$sheet) {
            $this->errors[] = 'Foglio "MODULI" non trovato';
            return [];
        }

        $moduli = [];
        $headers = [];

        foreach ($sheet->getRowIterator(1) as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                // Normalizza il valore in stringa
                if (is_object($value)) {
                    $value = $value->__toString();
                } elseif (is_array($value)) {
                    $value = '';  // Array vuoto o da formula -> stringa vuota
                } elseif ($value === null) {
                    $value = '';
                } else {
                    $value = (string)$value;
                }
                $cells[] = $value;
            }

            if ($rowIndex === 1) {
                // Header
                $headers = array_map(function($h) {
                    return str_replace('*', '', $h);
                }, $cells);
            } else {
                // Skip empty rows
                if (empty($cells[0]) && empty($cells[1])) {
                    continue;
                }

                $modulo = array_combine($headers, $cells);
                $moduli[] = $modulo;
            }
        }

        return $moduli;
    }

    /**
     * Leggi foglio DOCENTI & ESITI
     */
    private function leggiDocentiEsiti($spreadsheet)
    {
        $sheet = $spreadsheet->getSheetByName('👥 DOCENTI & ESITI');
        if (!$sheet) {
            $this->errors[] = 'Foglio "DOCENTI & ESITI" non trovato';
            return [];
        }

        $righe = [];
        $headers = [];

        foreach ($sheet->getRowIterator(1) as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                // Normalizza il valore in stringa
                if (is_object($value)) {
                    $value = $value->__toString();
                } elseif (is_array($value)) {
                    $value = '';  // Array vuoto o da formula -> stringa vuota
                } elseif ($value === null) {
                    $value = '';
                } else {
                    $value = (string)$value;
                }
                $cells[] = $value;
            }

            if ($rowIndex === 1) {
                $headers = array_map(function($h) {
                    return str_replace('*', '', $h);
                }, $cells);
            } else {
                if (empty($cells[0]) && empty($cells[1]) && empty($cells[2])) {
                    continue;
                }

                $riga = array_combine($headers, $cells);
                $righe[] = $riga;
            }
        }

        return $righe;
    }

    /**
     * Valida dati prima dell'import
     */
    private function validaDati($infoProgetto, $moduli, $docentiEsiti)
    {
        $isValid = true;

        // Valida info progetto
        if (empty($infoProgetto['CODICE_PROGETTO'])) {
            $this->errors[] = 'CODICE_PROGETTO è obbligatorio';
            $isValid = false;
        }

        if (empty($infoProgetto['TITOLO_PROGETTO'])) {
            $this->errors[] = 'TITOLO_PROGETTO è obbligatorio';
            $isValid = false;
        }

        // Valida moduli
        if (empty($moduli)) {
            $this->errors[] = 'Nessun modulo trovato nel foglio MODULI';
            $isValid = false;
        }

        foreach ($moduli as $idx => $modulo) {
            if (empty($modulo['CODICE'])) {
                $this->errors[] = "Modulo riga " . ($idx + 2) . ": CODICE obbligatorio";
                $isValid = false;
            }
            if (empty($modulo['TITOLO'])) {
                $this->errors[] = "Modulo riga " . ($idx + 2) . ": TITOLO obbligatorio";
                $isValid = false;
            }
        }

        return $isValid && empty($this->errors);
    }

    /**
     * Esegui import completo
     */
    private function eseguiImport($infoProgetto, $moduli, $docentiEsiti, $dryRun = false, $overwrite = false)
    {
        // 0. Se overwrite è abilitato, elimina il progetto esistente
        if ($overwrite) {
            $codiceProgetto = $infoProgetto['CODICE_PROGETTO'];
            // Cerca anche tra i record soft-deleted per evitare conflitti UNIQUE
            $progettoEsistente = Progetto::findWithDeleted()->andWhere(['codice_progetto' => $codiceProgetto])->one();

            if ($progettoEsistente) {
                $this->log[] = "⚠️  Progetto esistente trovato: {$progettoEsistente->titolo} (ID: {$progettoEsistente->id})";
                $this->log[] = "🗑️  Eliminazione in corso...";

                // Elimina in cascata usando SQL diretto per evitare problemi con soft delete
                // deleteAll() fa DELETE fisico via SQL, bypassa completamente ActiveRecord/SoftDelete
                $progettoId = $progettoEsistente->id;

                // Conta record da eliminare (usando findWithDeleted per contare TUTTO)
                $avvisoIds = Avviso::findWithDeleted()->where(['progetto_id' => $progettoId])->select('id')->column();
                $moduloIds = !empty($avvisoIds) ? Modulo::findWithDeleted()->where(['avviso_id' => $avvisoIds])->select('id')->column() : [];
                $istanzaIds = !empty($moduloIds) ? Istanza::findWithDeleted()->where(['modulo_id' => $moduloIds])->select('id')->column() : [];
                if (empty($istanzaIds)) {
                    $istanzaIds = !empty($avvisoIds) ? Istanza::findWithDeleted()->where(['avviso_id' => $avvisoIds])->select('id')->column() : [];
                }
                $assegnazioneIds = !empty($moduloIds) ? Assegnazione::findWithDeleted()->where(['modulo_id' => $moduloIds])->select('id')->column() : [];
                $contrattoIds = !empty($assegnazioneIds) ? Contratto::findWithDeleted()->where(['assegnazione_id' => $assegnazioneIds])->select('id')->column() : [];

                $countPagamenti = !empty($contrattoIds) ? Pagamento::findWithDeleted()->where(['contratto_id' => $contrattoIds])->count() : 0;
                $countContratti = count($contrattoIds);
                $countAssegnazioni = count($assegnazioneIds);
                $countIstanze = count($istanzaIds);
                $countModuli = count($moduloIds);

                $this->log[] = "   - Pagamenti: {$countPagamenti}";
                $this->log[] = "   - Contratti: {$countContratti}";
                $this->log[] = "   - Assegnazioni: {$countAssegnazioni}";
                $this->log[] = "   - Istanze: {$countIstanze}";
                $this->log[] = "   - Moduli: {$countModuli}";
                $this->log[] = "   - Avvisi: " . count($avvisoIds);

                // Elimina fisicamente con deleteAll (SQL DELETE diretto, bypassa soft delete)
                if (!empty($contrattoIds)) {
                    Pagamento::deleteAll(['contratto_id' => $contrattoIds]);
                }
                if (!empty($assegnazioneIds)) {
                    Contratto::deleteAll(['assegnazione_id' => $assegnazioneIds]);
                }
                if (!empty($moduloIds)) {
                    Assegnazione::deleteAll(['modulo_id' => $moduloIds]);
                    Istanza::deleteAll(['modulo_id' => $moduloIds]);
                }
                if (!empty($avvisoIds)) {
                    Istanza::deleteAll(['avviso_id' => $avvisoIds]);
                    Modulo::deleteAll(['avviso_id' => $avvisoIds]);
                }
                Avviso::deleteAll(['progetto_id' => $progettoId]);
                Yii::$app->db->createCommand()->delete('progetto', ['id' => $progettoId])->execute();

                $this->log[] = "✓ Progetto esistente eliminato completamente";
            }
        }

        // 1. Crea/trova fonte finanziamento
        $fonteCodice = $infoProgetto['FONTE_FINANZIAMENTO'] ?? 'PNRR';
        $fonte = FonteFinanziamento::findOne(['codice' => $fonteCodice]);
        if (!$fonte) {
            $this->errors[] = "Fonte finanziamento '$fonteCodice' non trovata";
            return false;
        }
        $this->log[] = "✓ Fonte finanziamento: {$fonte->descrizione}";

        // 2. Crea/trova sede
        $sedeNome = $infoProgetto['SEDE_NOME'] ?? 'Sede Principale';
        $sede = Sede::findOne(['nome' => $sedeNome]);
        if (!$sede) {
            $sede = new Sede([
                'nome' => $sedeNome,
                'indirizzo' => $infoProgetto['SEDE_INDIRIZZO'] ?? '',
                'citta' => $infoProgetto['SEDE_CITTA'] ?? '',
                'provincia' => $infoProgetto['SEDE_PROVINCIA'] ?? '',
            ]);
            if (!$sede->save()) {
                $this->errors[] = "Errore creazione sede: " . json_encode($sede->errors);
                return false;
            }
            $this->log[] = "✓ Sede creata: {$sede->nome}";
        } else {
            $this->log[] = "✓ Sede esistente: {$sede->nome}";
        }

        // 3. Crea progetto
        $progetto = new Progetto([
            'fonte_finanziamento_id' => $fonte->id,
            'codice_progetto' => $infoProgetto['CODICE_PROGETTO'],
            'titolo' => $infoProgetto['TITOLO_PROGETTO'],
            'budget_totale' => $infoProgetto['BUDGET_TOTALE'] ?? null,
            'data_inizio' => $infoProgetto['DATA_INIZIO'] ?? null,
            'data_fine' => $infoProgetto['DATA_FINE'] ?? null,
            'numero_studenti' => $infoProgetto['NUMERO_STUDENTI'] ?? 20,
            'status' => 'attivo',
        ]);

        if (!$progetto->save()) {
            $this->errors[] = "Errore creazione progetto: " . json_encode($progetto->errors);
            return false;
        }
        $this->log[] = "✓ Progetto creato: {$progetto->titolo} (ID: {$progetto->id})";

        // 4. Crea avviso
        $avviso = new Avviso([
            'progetto_id' => $progetto->id,
            'codice_avviso' => $infoProgetto['CODICE_AVVISO'] ?? 'AVV-001',
            'titolo' => $infoProgetto['TITOLO_AVVISO'] ?? $progetto->titolo,
            'data_pubblicazione' => $infoProgetto['DATA_PUBBLICAZIONE'] ?? null,
            'data_scadenza' => $infoProgetto['DATA_SCADENZA'] ?? null,
            'status' => 'chiuso',
        ]);

        if (!$avviso->save()) {
            $this->errors[] = "Errore creazione avviso: " . json_encode($avviso->errors);
            return false;
        }
        $this->log[] = "✓ Avviso creato: {$avviso->titolo} (ID: {$avviso->id})";

        // 5. Crea moduli
        $moduliMap = []; // codice => id
        foreach ($moduli as $moduloData) {
            $modulo = new Modulo([
                'avviso_id' => $avviso->id,
                'sede_id' => $sede->id,
                'codice' => $moduloData['CODICE'],
                'titolo' => $moduloData['TITOLO'],
                'descrizione' => $moduloData['DESCRIZIONE'] ?? null,
                'filiera' => $moduloData['FILIERA'] ?? null,
                'ore_totali' => $moduloData['ORE_TOTALI'] ?? 20,
                'compenso_orario' => $moduloData['COMPENSO_ORARIO'] ?? 60,
                'data_inizio' => $moduloData['DATA_INIZIO'] ?? null,
                'data_fine' => $moduloData['DATA_FINE'] ?? null,
                'max_allievi' => $moduloData['MAX_ALLIEVI'] ?? 20,
                'status' => 'concluso',
            ]);

            if (!$modulo->save()) {
                $this->errors[] = "Errore creazione modulo {$moduloData['CODICE']}: " . json_encode($modulo->errors);
                return false;
            }

            $moduliMap[$moduloData['CODICE']] = $modulo->id;
            $this->log[] = "  ✓ Modulo: {$modulo->titolo}";
        }

        $this->log[] = "✓ Creati " . count($moduli) . " moduli";

        // 6. Processa docenti e assegnazioni
        $docentiMap = []; // nome completo => codice_fiscale

        foreach ($docentiEsiti as $idx => $riga) {
            $codiceModulo = $riga['CODICE_MODULO'];

            // CODICE_MODULO è obbligatorio
            if (empty($codiceModulo)) {
                $this->warnings[] = "Riga " . ($idx + 2) . ": CODICE_MODULO obbligatorio";
                continue;
            }

            if (!isset($moduliMap[$codiceModulo])) {
                $this->warnings[] = "Riga " . ($idx + 2) . ": Modulo '$codiceModulo' non trovato";
                continue;
            }

            $moduloId = $moduliMap[$codiceModulo];

            // Provenienza
            $provenienza = !empty($riga['PROVENIENZA']) ? $riga['PROVENIENZA'] : 'TP';
            if ($provenienza === 'P.P.') $provenienza = 'TP';
            if ($provenienza === 'U.R.') $provenienza = 'RU';
            // Se è ancora vuoto o non valido, usa TP di default
            if (!in_array($provenienza, ['TP', 'RU'])) {
                $provenienza = 'TP';
            }

            // ASSEGNATARIO
            if (!empty($riga['ASSEGNATARIO'])) {
                $nomeAssegnatario = trim(str_replace('(Rinuncia)', '', $riga['ASSEGNATARIO']));
                $hasRinuncia = strpos($riga['ASSEGNATARIO'], '(Rinuncia)') !== false;

                $docenteCF = $this->creaOTrovaDocente($nomeAssegnatario, $provenienza, $docentiMap);

                // Crea istanza assegnato
                $istanza = $this->creaIstanza($avviso->id, $moduloId, $docenteCF, 'assegnato', $riga);

                if ($istanza && $hasRinuncia) {
                    // Rinuncia: aggiorna status istanza e registra nello storico
                    $istanza->status = 'rinuncia';
                    $istanza->save(false);
                    $storicoRinuncia = new \app\models\StoricoEsito([
                        'istanza_id' => $istanza->id,
                        'esito' => 'rinuncia',
                        'data_esito' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
                        'note' => 'Rinuncia importata da file Excel',
                    ]);
                    $storicoRinuncia->save(false);
                } elseif ($istanza) {
                    // Crea assegnazione
                    $this->creaAssegnazione($moduloId, $docenteCF, $istanza->id, $riga);
                }
            }

            // IDONEI
            if (!empty($riga['IDONEI'])) {
                $idonei = array_map('trim', explode(',', $riga['IDONEI']));
                foreach ($idonei as $nomeIdoneo) {
                    if (empty($nomeIdoneo)) continue;

                    $docenteCF = $this->creaOTrovaDocente($nomeIdoneo, $provenienza, $docentiMap);
                    $this->creaIstanza($avviso->id, $moduloId, $docenteCF, 'idoneo', $riga);
                }
            }

            // NON IDONEI
            if (!empty($riga['NON_IDONEI'])) {
                $nonIdonei = array_map('trim', explode(',', $riga['NON_IDONEI']));
                foreach ($nonIdonei as $entry) {
                    if (empty($entry)) continue;

                    // Formato: "Nome Cognome (Motivazione)"
                    preg_match('/^(.+?)\s*\((.+)\)$/', $entry, $matches);
                    $nomeNonIdoneo = $matches[1] ?? $entry;
                    $motivazione = $matches[2] ?? 'Non specificata';

                    $docenteCF = $this->creaOTrovaDocente($nomeNonIdoneo, $provenienza, $docentiMap);
                    $this->creaIstanza($avviso->id, $moduloId, $docenteCF, 'non_idoneo', $riga, $motivazione);
                }
            }
        }

        $this->log[] = "✓ Import completato con successo!";
        $this->log[] = "  - Docenti gestiti: " . count($docentiMap);
        $this->log[] = "  - Moduli: " . count($moduliMap);

        return true;
    }

    /**
     * Crea o trova docente
     */
    private function creaOTrovaDocente($nomeCompleto, $provenienza, &$docentiMap)
    {
        if (isset($docentiMap[$nomeCompleto])) {
            return $docentiMap[$nomeCompleto];
        }

        // Split nome cognome
        $parts = explode(' ', trim($nomeCompleto), 2);
        $nome = $parts[0] ?? '';
        $cognome = $parts[1] ?? $parts[0];

        // Genera CF fittizio
        $cf = $this->generaCodiceFiscaleFittizio($cognome, $nome);

        // Cerca se esiste già
        $docente = Docente::findOne(['codice_fiscale' => $cf]);

        if (!$docente) {
            $docente = new Docente([
                'codice_fiscale' => $cf,
                'cognome' => $cognome,
                'nome' => $nome,
                'provenienza' => $provenienza,
            ]);

            if (!$docente->save()) {
                $this->warnings[] = "Errore creazione docente $nomeCompleto: " . json_encode($docente->errors);
                return null;
            }
        }

        $docentiMap[$nomeCompleto] = $cf;
        return $cf;
    }

    /**
     * Genera codice fiscale fittizio
     */
    private function generaCodiceFiscaleFittizio($cognome, $nome)
    {
        $cognome = strtoupper(preg_replace('/[^A-Z]/i', '', $cognome));
        $nome = strtoupper(preg_replace('/[^A-Z]/i', '', $nome));

        $cf = substr($cognome . 'XXX', 0, 3);
        $cf .= substr($nome . 'YYY', 0, 3);
        $cf .= '99A99Z999X'; // Parte fissa per CF fittizi

        return strtoupper($cf);
    }

    /**
     * Crea istanza
     */
    private function creaIstanza($avvisoId, $moduloId, $docenteCF, $esitoCTS, $riga, $motivazione = null)
    {
        if (!$docenteCF) return null;

        // Verifica se esiste già un'istanza per questo docente/modulo/avviso
        $istanza = Istanza::findOne([
            'avviso_id' => $avvisoId,
            'modulo_id' => $moduloId,
            'docente_codice_fiscale' => $docenteCF,
        ]);

        if ($istanza) {
            // Istanza già esistente - aggiorna solo se l'esito è migliore
            // Priorità: assegnato > idoneo > non_idoneo
            $priorita = ['assegnato' => 3, 'idoneo' => 2, 'non_idoneo' => 1];
            $prioritaCorrente = $priorita[$istanza->esito_cts] ?? 0;
            $prioritaNuova = $priorita[$esitoCTS] ?? 0;

            if ($prioritaNuova > $prioritaCorrente) {
                $istanza->esito_cts = $esitoCTS;
                $istanza->note_valutazione = $motivazione;
                $istanza->save();
            }

            return $istanza;
        }

        // Crea nuova istanza
        $istanza = new Istanza([
            'avviso_id' => $avvisoId,
            'modulo_id' => $moduloId,
            'docente_codice_fiscale' => $docenteCF,
            'data_presentazione' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
            'esito_cts' => $esitoCTS,
            'status' => 'valutata',
            'note_valutazione' => $motivazione,
        ]);

        if (!$istanza->save()) {
            $this->warnings[] = "Errore creazione istanza per docente $docenteCF: " . json_encode($istanza->errors);
            return null;
        }

        // Crea record storico esito
        $storico = new \app\models\StoricoEsito([
            'istanza_id' => $istanza->id,
            'esito' => $esitoCTS,
            'data_esito' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
            'note' => $motivazione,
        ]);
        $storico->save(false);

        return $istanza;
    }

    /**
     * Crea assegnazione e contratto
     */
    private function creaAssegnazione($moduloId, $docenteCF, $istanzaId, $riga)
    {
        // Verifica se esiste già un'assegnazione per questo docente/modulo
        $assegnazione = Assegnazione::findOne([
            'modulo_id' => $moduloId,
            'docente_codice_fiscale' => $docenteCF,
        ]);

        if ($assegnazione) {
            // Assegnazione già esistente - skippa creazione contratto
            return;
        }

        // Crea nuova assegnazione
        $assegnazione = new Assegnazione([
            'modulo_id' => $moduloId,
            'docente_codice_fiscale' => $docenteCF,
            'istanza_id' => $istanzaId,
            'data_assegnazione' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
        ]);

        if (!$assegnazione->save()) {
            $this->warnings[] = "Errore creazione assegnazione: " . json_encode($assegnazione->errors);
            return;
        }

        // Mappa tipo contratto
        $tipoContratto = 'cococo';
        $tipoInput = strtoupper($riga['TIPO_CONTRATTO'] ?? '');

        if (strpos($tipoInput, 'P.P.') !== false || strpos($tipoInput, 'PRESTAZIONE') !== false) {
            $tipoContratto = 'partita_iva';
        } elseif (strpos($tipoInput, 'OCCASIONALE') !== false) {
            $tipoContratto = 'occasionale';
        }

        // Crea contratto
        $contratto = new Contratto([
            'assegnazione_id' => $assegnazione->id,
            'tipo' => $tipoContratto,
            'numero_contratto' => 'CONTR-' . $assegnazione->id,
            'data_stipula' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
            'data_inizio' => date('Y-m-d'),
            'data_fine' => date('Y-m-d', strtotime('+6 months')),
            'compenso_orario' => $riga['COMPENSO_ORARIO'] ?? 60,
            'ore_contrattualizzate' => $riga['ORE_ASSEGNATE'] ?? 20,
            'compenso_totale' => ($riga['COMPENSO_ORARIO'] ?? 60) * ($riga['ORE_ASSEGNATE'] ?? 20),
            'status' => 'concluso',
        ]);

        if (!$contratto->save()) {
            $this->warnings[] = "Errore creazione contratto: " . json_encode($contratto->errors);
        }
    }

    /**
     * Download template
     */
    public function actionDownloadTemplate()
    {
        $filePath = Yii::getAlias('@app/docs/TEMPLATE_IMPORT_DATI.xlsx');

        if (!file_exists($filePath)) {
            Yii::$app->session->setFlash('error', 'Template non trovato');
            return $this->redirect(['index']);
        }

        return Yii::$app->response->sendFile($filePath, 'TEMPLATE_IMPORT_ALBATROS.xlsx');
    }

    /**
     * Download template assegnazioni XLSX generato programmaticamente
     */
    public function actionDownloadTemplateAssegnazioni()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // --- Foglio ISTRUZIONI ---
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');

        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT ASSEGNAZIONI');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $istruzioni->setCellValue('A3', 'Questo template permette di importare assegnazioni docenti ai moduli esistenti.');
        $istruzioni->setCellValue('A5', 'ISTRUZIONI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', '1. Compilare il foglio "ASSEGNAZIONI" con i dati richiesti');
        $istruzioni->setCellValue('A7', '2. I campi con * sono obbligatori');
        $istruzioni->setCellValue('A8', '3. CODICE_MODULO deve corrispondere a un modulo esistente nel sistema');
        $istruzioni->setCellValue('A9', '4. Se CODICE_FISCALE è fornito, il docente verrà cercato per CF; altrimenti per COGNOME+NOME');
        $istruzioni->setCellValue('A10', '5. Se il docente non esiste, verrà creato automaticamente');
        $istruzioni->setCellValue('A11', '6. PROVENIENZA: TP = Terza Parte/Professionista (€60/h), RU = Ricercatore Universitario (€70/h)');
        $istruzioni->setCellValue('A12', '7. TIPO_CONTRATTO: partita_iva, cococo (default), occasionale');
        $istruzioni->setCellValue('A13', '8. La riga 2 in grigio è un esempio e verrà ignorata dall\'import');
        $istruzioni->setCellValue('A15', 'CAMPI:');
        $istruzioni->getStyle('A15')->getFont()->setBold(true);
        $istruzioni->setCellValue('A16', 'CODICE_MODULO* - Codice del modulo esistente');
        $istruzioni->setCellValue('A17', 'COGNOME* - Cognome del docente');
        $istruzioni->setCellValue('A18', 'NOME* - Nome del docente');
        $istruzioni->setCellValue('A19', 'CODICE_FISCALE - CF del docente (se vuoto verrà generato)');
        $istruzioni->setCellValue('A20', 'PROVENIENZA - TP o RU (default: TP)');
        $istruzioni->setCellValue('A21', 'COMPENSO_ORARIO - Compenso orario in euro (default: da modulo)');
        $istruzioni->setCellValue('A22', 'ORE_ASSEGNATE - Ore assegnate (default: ore_totali del modulo)');
        $istruzioni->setCellValue('A23', 'TIPO_CONTRATTO - partita_iva, cococo, occasionale (default: cococo)');
        $istruzioni->setCellValue('A24', 'DATA_ASSEGNAZIONE - Data assegnazione (formato YYYY-MM-DD)');
        $istruzioni->setCellValue('A25', 'NOTE - Note aggiuntive');

        $istruzioni->getColumnDimension('A')->setWidth(80);

        // --- Foglio ASSEGNAZIONI ---
        $assegnazioni = $spreadsheet->createSheet();
        $assegnazioni->setTitle('ASSEGNAZIONI');

        $headers = [
            'A' => 'CODICE_MODULO*',
            'B' => 'COGNOME*',
            'C' => 'NOME*',
            'D' => 'CODICE_FISCALE',
            'E' => 'PROVENIENZA',
            'F' => 'COMPENSO_ORARIO',
            'G' => 'ORE_ASSEGNATE',
            'H' => 'TIPO_CONTRATTO',
            'I' => 'DATA_ASSEGNAZIONE',
            'J' => 'NOTE',
        ];

        // Header blu (#206BC4) con testo bianco grassetto
        foreach ($headers as $col => $header) {
            $assegnazioni->setCellValue($col . '1', $header);
            $assegnazioni->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '206BC4'],
                ],
            ]);
            $assegnazioni->getColumnDimension($col)->setAutoSize(true);
        }

        // Riga esempio in grigio
        $esempio = ['MOD-001', 'Rossi', 'Mario', 'RSSMRA80A01H501Z', 'TP', '60', '20', 'cococo', '2025-01-15', 'Esempio - cancellare questa riga'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $assegnazioni->setCellValue($col . '2', $esempio[$colIdx]);
            $assegnazioni->getStyle($col . '2')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
            ]);
            $colIdx++;
        }

        // Genera file
        $tempPath = Yii::getAlias('@runtime') . '/template_assegnazioni_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_ASSEGNAZIONI.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) {
                @unlink($tempPath);
            });
    }

    /**
     * Download template calendari XLSX generato programmaticamente
     */
    public function actionDownloadTemplateCalendari()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // --- Foglio ISTRUZIONI ---
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');

        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT CALENDARIO');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $istruzioni->setCellValue('A3', 'Questo template permette di importare il calendario lezioni per i moduli esistenti.');
        $istruzioni->setCellValue('A5', 'ISTRUZIONI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', '1. Compilare il foglio "CALENDARIO" con i dati richiesti');
        $istruzioni->setCellValue('A7', '2. I campi con * sono obbligatori');
        $istruzioni->setCellValue('A8', '3. CODICE_MODULO deve corrispondere a un modulo esistente nel sistema');
        $istruzioni->setCellValue('A9', '4. CF_DOCENTE deve corrispondere a un docente assegnato al modulo');
        $istruzioni->setCellValue('A10', '5. DATA in formato YYYY-MM-DD');
        $istruzioni->setCellValue('A11', '6. ORA_INIZIO e ORA_FINE in formato HH:MM');
        $istruzioni->setCellValue('A12', '7. STATUS: programmata (default), completata, annullata');
        $istruzioni->setCellValue('A13', '8. La riga 2 in grigio è un esempio e verrà ignorata dall\'import');

        $istruzioni->getColumnDimension('A')->setWidth(80);

        // --- Foglio CALENDARIO ---
        $calendario = $spreadsheet->createSheet();
        $calendario->setTitle('CALENDARIO');

        $headers = [
            'A' => 'CODICE_MODULO*',
            'B' => 'CF_DOCENTE*',
            'C' => 'DATA*',
            'D' => 'ORA_INIZIO*',
            'E' => 'ORA_FINE*',
            'F' => 'AULA',
            'G' => 'ARGOMENTO',
            'H' => 'DESCRIZIONE_ATTIVITA',
            'I' => 'STATUS',
            'J' => 'NOTE',
        ];

        // Header blu (#206BC4) con testo bianco grassetto
        foreach ($headers as $col => $header) {
            $calendario->setCellValue($col . '1', $header);
            $calendario->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '206BC4'],
                ],
            ]);
            $calendario->getColumnDimension($col)->setAutoSize(true);
        }

        // Riga esempio in grigio
        $esempio = ['MOD-001', 'RSSMRA80A01H501Z', '2025-02-01', '09:00', '13:00', 'Aula A1', 'Introduzione', 'Lezione introduttiva', 'programmata', 'Esempio - cancellare questa riga'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $calendario->setCellValue($col . '2', $esempio[$colIdx]);
            $calendario->getStyle($col . '2')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
            ]);
            $colIdx++;
        }

        // Genera file
        $tempPath = Yii::getAlias('@runtime') . '/template_calendari_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_CALENDARIO.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) {
                @unlink($tempPath);
            });
    }

    /**
     * Upload e import assegnazioni con flusso preview/conferma
     *
     * POST params:
     * - excel_file: file XLSX
     * - preview: 1 (default) = solo validazione e preview
     * - confirm: 1 = esegue import effettivo
     */
    public function actionUploadAssegnazioni()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) {
            return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        }

        $tempPath = Yii::getAlias('@runtime') . '/import_assegnazioni_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) {
            return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        }

        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('ASSEGNAZIONI');

            if (!$sheet) {
                @unlink($tempPath);
                return $this->asJson(['success' => false, 'error' => 'Foglio "ASSEGNAZIONI" non trovato nel file']);
            }

            $righe = $this->leggiRigheFoglio($sheet);

            if (empty($righe)) {
                @unlink($tempPath);
                return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati trovata nel foglio ASSEGNAZIONI']);
            }

            // Reset log/errors/warnings
            $this->log = [];
            $this->errors = [];
            $this->warnings = [];

            $preview = [];
            $stats = [
                'nuovi_docenti' => 0,
                'nuove_assegnazioni' => 0,
                'saltate' => 0,
                'errori' => 0,
            ];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3; // riga 1 = header, riga 2 = esempio, dati da riga 3
                $rowPreview = [
                    'riga' => $rowNum,
                    'codice_modulo' => $riga['CODICE_MODULO'] ?? '',
                    'cognome' => $riga['COGNOME'] ?? '',
                    'nome' => $riga['NOME'] ?? '',
                    'codice_fiscale' => $riga['CODICE_FISCALE'] ?? '',
                    'azioni' => [],
                    'stato' => 'ok', // ok, warning, errore
                ];

                // Validazione campi obbligatori
                $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                $cognome = trim($riga['COGNOME'] ?? '');
                $nome = trim($riga['NOME'] ?? '');

                if (empty($codiceModulo)) {
                    $this->errors[] = "Riga {$rowNum}: CODICE_MODULO obbligatorio";
                    $rowPreview['stato'] = 'errore';
                    $rowPreview['azioni'][] = 'CODICE_MODULO mancante';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                if (empty($cognome) || empty($nome)) {
                    $this->errors[] = "Riga {$rowNum}: COGNOME e NOME obbligatori";
                    $rowPreview['stato'] = 'errore';
                    $rowPreview['azioni'][] = 'COGNOME/NOME mancanti';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                // Cerca modulo
                $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                if (!$modulo) {
                    $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato nel sistema";
                    $rowPreview['stato'] = 'errore';
                    $rowPreview['azioni'][] = "Modulo '{$codiceModulo}' non trovato";
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                $rowPreview['modulo_titolo'] = $modulo->titolo;
                $rowPreview['azioni'][] = "Modulo trovato: {$modulo->titolo} (ID: {$modulo->id})";

                // Cerca docente
                $cf = trim($riga['CODICE_FISCALE'] ?? '');
                $docente = null;
                $docenteNuovo = false;

                if (!empty($cf)) {
                    $docente = Docente::findOne(['codice_fiscale' => strtoupper($cf)]);
                    if ($docente) {
                        $rowPreview['azioni'][] = "Docente esistente: {$docente->cognome} {$docente->nome} (CF: {$docente->codice_fiscale})";
                    }
                }

                if (!$docente) {
                    // Cerca per cognome+nome
                    $docente = Docente::findOne(['cognome' => $cognome, 'nome' => $nome]);
                    if ($docente) {
                        $rowPreview['azioni'][] = "Docente trovato per nome: {$docente->cognome} {$docente->nome} (CF: {$docente->codice_fiscale})";
                        if (empty($cf)) {
                            $cf = $docente->codice_fiscale;
                        }
                    }
                }

                if (!$docente) {
                    $docenteNuovo = true;
                    if (empty($cf)) {
                        $cf = $this->generaCodiceFiscaleFittizio($cognome, $nome);
                    }
                    $cf = strtoupper($cf);
                    $rowPreview['azioni'][] = "Nuovo docente da creare: {$cognome} {$nome} (CF: {$cf})";
                    $stats['nuovi_docenti']++;
                } else {
                    $cf = $docente->codice_fiscale;
                }

                $rowPreview['codice_fiscale'] = $cf;

                // Verifica se assegnazione esiste già
                $assegnazioneEsistente = Assegnazione::findOne([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                ]);

                if ($assegnazioneEsistente) {
                    $rowPreview['stato'] = 'warning';
                    $rowPreview['azioni'][] = 'Assegnazione già esistente - verrà saltata';
                    $this->warnings[] = "Riga {$rowNum}: Assegnazione già esistente per {$cognome} {$nome} al modulo {$codiceModulo}";
                    $stats['saltate']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                // Calcola dettagli contratto per preview
                $provenienza = strtoupper(trim($riga['PROVENIENZA'] ?? 'TP'));
                if ($provenienza === 'P.P.') $provenienza = 'TP';
                if ($provenienza === 'U.R.') $provenienza = 'RU';
                if (!in_array($provenienza, ['TP', 'RU'])) $provenienza = 'TP';

                $compensoOrario = !empty($riga['COMPENSO_ORARIO']) ? (float)$riga['COMPENSO_ORARIO'] : (float)($modulo->compenso_orario ?? 60);
                $oreAssegnate = !empty($riga['ORE_ASSEGNATE']) ? (float)$riga['ORE_ASSEGNATE'] : (float)($modulo->ore_totali ?? 20);

                $tipoContratto = 'cococo';
                $tipoInput = strtoupper(trim($riga['TIPO_CONTRATTO'] ?? ''));
                if (strpos($tipoInput, 'PARTITA') !== false || $tipoInput === 'PARTITA_IVA') {
                    $tipoContratto = 'partita_iva';
                } elseif (strpos($tipoInput, 'OCCASIONALE') !== false) {
                    $tipoContratto = 'occasionale';
                } elseif (!empty($tipoInput) && in_array(strtolower($tipoInput), ['partita_iva', 'cococo', 'occasionale'])) {
                    $tipoContratto = strtolower($tipoInput);
                }

                $dataAssegnazione = !empty($riga['DATA_ASSEGNAZIONE']) ? $riga['DATA_ASSEGNAZIONE'] : date('Y-m-d');

                $rowPreview['azioni'][] = "Nuova assegnazione: {$oreAssegnate}h x €{$compensoOrario}/h = €" . ($oreAssegnate * $compensoOrario);
                $rowPreview['azioni'][] = "Contratto: {$tipoContratto}, provenienza: {$provenienza}";
                $stats['nuove_assegnazioni']++;

                $preview[] = $rowPreview;
            }

            // FASE 1: Preview
            if (!$confirm) {
                @unlink($tempPath);
                return $this->asJson([
                    'success' => true,
                    'mode' => 'preview',
                    'preview' => $preview,
                    'errors' => $this->errors,
                    'warnings' => $this->warnings,
                    'stats' => $stats,
                ]);
            }

            // FASE 2: Import effettivo
            if (!empty($this->errors)) {
                @unlink($tempPath);
                return $this->asJson([
                    'success' => false,
                    'error' => 'Impossibile procedere con errori bloccanti. Correggere il file e riprovare.',
                    'errors' => $this->errors,
                    'warnings' => $this->warnings,
                    'stats' => $stats,
                ]);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                $saltati = 0;

                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                    $cognome = trim($riga['COGNOME'] ?? '');
                    $nome = trim($riga['NOME'] ?? '');

                    if (empty($codiceModulo) || empty($cognome) || empty($nome)) {
                        continue;
                    }

                    $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                    if (!$modulo) {
                        continue;
                    }

                    // Trova o crea docente
                    $cf = trim($riga['CODICE_FISCALE'] ?? '');
                    $docente = null;

                    if (!empty($cf)) {
                        $docente = Docente::findOne(['codice_fiscale' => strtoupper($cf)]);
                    }
                    if (!$docente) {
                        $docente = Docente::findOne(['cognome' => $cognome, 'nome' => $nome]);
                    }
                    if (!$docente) {
                        if (empty($cf)) {
                            $cf = $this->generaCodiceFiscaleFittizio($cognome, $nome);
                        }
                        $cf = strtoupper($cf);

                        $provenienza = strtoupper(trim($riga['PROVENIENZA'] ?? 'TP'));
                        if ($provenienza === 'P.P.') $provenienza = 'TP';
                        if ($provenienza === 'U.R.') $provenienza = 'RU';
                        if (!in_array($provenienza, ['TP', 'RU'])) $provenienza = 'TP';

                        $docente = new Docente([
                            'codice_fiscale' => $cf,
                            'cognome' => $cognome,
                            'nome' => $nome,
                            'provenienza' => $provenienza,
                        ]);
                        if (!$docente->save()) {
                            $this->warnings[] = "Riga {$rowNum}: Errore creazione docente: " . json_encode($docente->errors);
                            continue;
                        }
                        $this->log[] = "Docente creato: {$cognome} {$nome} (CF: {$cf})";
                    } else {
                        $cf = $docente->codice_fiscale;
                    }

                    // Verifica assegnazione esistente
                    $assegnazioneEsistente = Assegnazione::findOne([
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cf,
                    ]);
                    if ($assegnazioneEsistente) {
                        $saltati++;
                        continue;
                    }

                    // Crea istanza
                    $avvisoId = $modulo->avviso_id;
                    $istanza = new Istanza([
                        'avviso_id' => $avvisoId,
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cf,
                        'data_presentazione' => $riga['DATA_ASSEGNAZIONE'] ?? date('Y-m-d'),
                        'esito_cts' => 'assegnato',
                        'status' => 'valutata',
                    ]);
                    if (!$istanza->save()) {
                        $this->warnings[] = "Riga {$rowNum}: Errore creazione istanza: " . json_encode($istanza->errors);
                        continue;
                    }

                    // Storico esito
                    $storico = new \app\models\StoricoEsito([
                        'istanza_id' => $istanza->id,
                        'esito' => 'assegnato',
                        'data_esito' => $riga['DATA_ASSEGNAZIONE'] ?? date('Y-m-d'),
                        'note' => 'Importato da file Excel assegnazioni',
                    ]);
                    $storico->save(false);

                    // Crea assegnazione
                    $dataAssegnazione = !empty($riga['DATA_ASSEGNAZIONE']) ? $riga['DATA_ASSEGNAZIONE'] : date('Y-m-d');
                    $assegnazione = new Assegnazione([
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cf,
                        'istanza_id' => $istanza->id,
                        'data_assegnazione' => $dataAssegnazione,
                    ]);
                    if (!$assegnazione->save()) {
                        $this->warnings[] = "Riga {$rowNum}: Errore creazione assegnazione: " . json_encode($assegnazione->errors);
                        continue;
                    }

                    // Crea contratto
                    $compensoOrario = !empty($riga['COMPENSO_ORARIO']) ? (float)$riga['COMPENSO_ORARIO'] : (float)($modulo->compenso_orario ?? 60);
                    $oreAssegnate = !empty($riga['ORE_ASSEGNATE']) ? (float)$riga['ORE_ASSEGNATE'] : (float)($modulo->ore_totali ?? 20);

                    $tipoContratto = 'cococo';
                    $tipoInput = strtoupper(trim($riga['TIPO_CONTRATTO'] ?? ''));
                    if (strpos($tipoInput, 'PARTITA') !== false || $tipoInput === 'PARTITA_IVA') {
                        $tipoContratto = 'partita_iva';
                    } elseif (strpos($tipoInput, 'OCCASIONALE') !== false) {
                        $tipoContratto = 'occasionale';
                    } elseif (!empty($tipoInput) && in_array(strtolower($tipoInput), ['partita_iva', 'cococo', 'occasionale'])) {
                        $tipoContratto = strtolower($tipoInput);
                    }

                    $contratto = new Contratto([
                        'assegnazione_id' => $assegnazione->id,
                        'tipo' => $tipoContratto,
                        'numero_contratto' => 'CONTR-' . $assegnazione->id,
                        'data_stipula' => $dataAssegnazione,
                        'data_inizio' => $dataAssegnazione,
                        'data_fine' => date('Y-m-d', strtotime($dataAssegnazione . ' +6 months')),
                        'compenso_orario' => $compensoOrario,
                        'ore_contrattualizzate' => $oreAssegnate,
                        'compenso_totale' => $compensoOrario * $oreAssegnate,
                        'status' => 'attivo',
                    ]);
                    if (!$contratto->save()) {
                        $this->warnings[] = "Riga {$rowNum}: Errore creazione contratto: " . json_encode($contratto->errors);
                        continue;
                    }

                    $this->log[] = "Assegnazione creata: {$cognome} {$nome} → {$modulo->titolo} ({$oreAssegnate}h x €{$compensoOrario}/h)";
                    $importati++;
                }

                $transaction->commit();
                @unlink($tempPath);

                $this->log[] = "Import completato: {$importati} assegnazioni create, {$saltati} saltate";

                return $this->asJson([
                    'success' => true,
                    'mode' => 'import',
                    'log' => $this->log,
                    'warnings' => $this->warnings,
                    'stats' => [
                        'nuove_assegnazioni' => $importati,
                        'saltate' => $saltati,
                    ],
                ]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                @unlink($tempPath);
                throw $e;
            }

        } catch (\Throwable $e) {
            @unlink($tempPath);
            Yii::error('Import assegnazioni error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => 'Errore durante l\'import: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Upload e import calendari da file Excel
     * Fase 1 (preview): POST con preview=1 → valida e restituisce JSON con preview
     * Fase 2 (confirm): POST con confirm=1 → esegue import in transazione DB
     */
    public function actionUploadCalendari()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) {
            return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        }

        $tempPath = Yii::getAlias('@runtime') . '/import_calendari_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) {
            return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        }

        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('CALENDARIO');

            if (!$sheet) {
                @unlink($tempPath);
                return $this->asJson(['success' => false, 'error' => 'Foglio "CALENDARIO" non trovato nel file']);
            }

            $righe = $this->leggiRigheFoglio($sheet);

            if (empty($righe)) {
                @unlink($tempPath);
                return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati trovata nel foglio CALENDARIO']);
            }

            // Reset log/errors/warnings
            $this->log = [];
            $this->errors = [];
            $this->warnings = [];

            $preview = [];
            $stats = [
                'nuove' => 0,
                'duplicate' => 0,
                'errori' => 0,
            ];

            $validStatuses = ['programmata', 'completata', 'annullata', 'recupero'];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3; // riga 1 = header, riga 2 = esempio, dati da riga 3
                $rowPreview = [
                    'riga' => $rowNum,
                    'codice_modulo' => '',
                    'modulo_titolo' => '',
                    'modulo_trovato' => false,
                    'cf_docente' => '',
                    'docente_nome' => '',
                    'docente_trovato' => false,
                    'data' => '',
                    'ora_inizio' => '',
                    'ora_fine' => '',
                    'ore' => 0,
                    'duplicata' => false,
                    'azione' => 'CREA',
                ];

                // Validazione CODICE_MODULO
                $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                $rowPreview['codice_modulo'] = $codiceModulo;

                if (empty($codiceModulo)) {
                    $this->errors[] = "Riga {$rowNum}: CODICE_MODULO obbligatorio";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                if (!$modulo) {
                    $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato nel sistema";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }
                $rowPreview['modulo_titolo'] = $modulo->titolo;
                $rowPreview['modulo_trovato'] = true;

                // Validazione CF_DOCENTE
                $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? ''));
                $rowPreview['cf_docente'] = $cfDocente;

                if (empty($cfDocente)) {
                    $this->errors[] = "Riga {$rowNum}: CF_DOCENTE obbligatorio";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                $docente = Docente::findOne(['codice_fiscale' => $cfDocente]);
                if (!$docente) {
                    $this->errors[] = "Riga {$rowNum}: Docente con CF '{$cfDocente}' non trovato nel sistema";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }
                $rowPreview['docente_nome'] = $docente->cognome . ' ' . $docente->nome;
                $rowPreview['docente_trovato'] = true;

                // Validazione e normalizzazione DATA
                $data = $riga['DATA'] ?? '';
                if (empty($data)) {
                    $this->errors[] = "Riga {$rowNum}: DATA obbligatoria";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                if (is_numeric($data)) {
                    $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$data)->format('Y-m-d');
                } elseif (strpos($data, '/') !== false) {
                    $parts = explode('/', $data);
                    if (count($parts) === 3) {
                        $data = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                }
                $rowPreview['data'] = $data;

                // Validazione ORA_INIZIO e ORA_FINE
                $oraInizio = $this->normalizzaOrario($riga['ORA_INIZIO'] ?? '');
                $oraFine = $this->normalizzaOrario($riga['ORA_FINE'] ?? '');

                if (!$oraInizio) {
                    $this->errors[] = "Riga {$rowNum}: ORA_INIZIO obbligatoria o formato non valido";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }
                if (!$oraFine) {
                    $this->errors[] = "Riga {$rowNum}: ORA_FINE obbligatoria o formato non valido";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                $rowPreview['ora_inizio'] = $oraInizio;
                $rowPreview['ora_fine'] = $oraFine;

                // Calcola ore dalla differenza
                $inizio = strtotime("2000-01-01 {$oraInizio}");
                $fine = strtotime("2000-01-01 {$oraFine}");
                $ore = ($fine - $inizio) / 3600;
                if ($ore <= 0) {
                    $this->errors[] = "Riga {$rowNum}: ORA_FINE deve essere successiva a ORA_INIZIO";
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }
                $rowPreview['ore'] = round($ore, 2);

                // Validazione STATUS
                $status = strtolower(trim($riga['STATUS'] ?? ''));
                if (empty($status)) {
                    $status = 'programmata';
                }
                if (!in_array($status, $validStatuses)) {
                    $this->errors[] = "Riga {$rowNum}: STATUS '{$status}' non valido. Valori ammessi: " . implode(', ', $validStatuses);
                    $rowPreview['azione'] = 'ERRORE';
                    $stats['errori']++;
                    $preview[] = $rowPreview;
                    continue;
                }

                // Verifica duplicata (stessa data+modulo+docente+ora_inizio)
                $duplicata = Calendario::findOne([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cfDocente,
                    'data' => $data,
                    'ora_inizio' => $oraInizio,
                ]);

                if ($duplicata) {
                    $rowPreview['duplicata'] = true;
                    $rowPreview['azione'] = 'SALTA';
                    $this->warnings[] = "Riga {$rowNum}: Lezione già esistente per {$data} {$oraInizio} - modulo {$codiceModulo} - docente {$cfDocente}";
                    $stats['duplicate']++;
                } else {
                    $stats['nuove']++;
                }

                $preview[] = $rowPreview;
            }

            // FASE 1: Preview
            if (!$confirm) {
                @unlink($tempPath);
                return $this->asJson([
                    'success' => true,
                    'mode' => 'preview',
                    'preview' => $preview,
                    'errors' => $this->errors,
                    'warnings' => $this->warnings,
                    'stats' => $stats,
                ]);
            }

            // FASE 2: Import effettivo
            if (!empty($this->errors)) {
                @unlink($tempPath);
                return $this->asJson([
                    'success' => false,
                    'error' => 'Impossibile procedere con errori bloccanti. Correggere il file e riprovare.',
                    'errors' => $this->errors,
                    'warnings' => $this->warnings,
                    'stats' => $stats,
                ]);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                $saltati = 0;

                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                    $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? ''));

                    if (empty($codiceModulo) || empty($cfDocente)) {
                        continue;
                    }

                    $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                    if (!$modulo) {
                        continue;
                    }

                    $docente = Docente::findOne(['codice_fiscale' => $cfDocente]);
                    if (!$docente) {
                        continue;
                    }

                    // Normalizza data
                    $data = $riga['DATA'] ?? '';
                    if (empty($data)) {
                        continue;
                    }
                    if (is_numeric($data)) {
                        $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$data)->format('Y-m-d');
                    } elseif (strpos($data, '/') !== false) {
                        $parts = explode('/', $data);
                        if (count($parts) === 3) {
                            $data = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                        }
                    }

                    // Normalizza orari
                    $oraInizio = $this->normalizzaOrario($riga['ORA_INIZIO'] ?? '');
                    $oraFine = $this->normalizzaOrario($riga['ORA_FINE'] ?? '');
                    if (!$oraInizio || !$oraFine) {
                        continue;
                    }

                    // Calcola ore
                    $inizio = strtotime("2000-01-01 {$oraInizio}");
                    $fine = strtotime("2000-01-01 {$oraFine}");
                    $ore = ($fine - $inizio) / 3600;
                    if ($ore <= 0) {
                        continue;
                    }

                    // Status
                    $status = strtolower(trim($riga['STATUS'] ?? ''));
                    if (empty($status) || !in_array($status, $validStatuses)) {
                        $status = 'programmata';
                    }

                    // Verifica duplicata
                    $duplicata = Calendario::findOne([
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cfDocente,
                        'data' => $data,
                        'ora_inizio' => $oraInizio,
                    ]);
                    if ($duplicata) {
                        $saltati++;
                        continue;
                    }

                    // Crea record Calendario
                    $calendario = new Calendario([
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cfDocente,
                        'data' => $data,
                        'ora_inizio' => $oraInizio,
                        'ora_fine' => $oraFine,
                        'ore' => round($ore, 2),
                        'aula' => trim($riga['AULA'] ?? ''),
                        'argomento' => trim($riga['ARGOMENTO'] ?? ''),
                        'descrizione_attivita' => trim($riga['DESCRIZIONE_ATTIVITA'] ?? ''),
                        'status' => $status,
                        'note' => trim($riga['NOTE'] ?? ''),
                    ]);

                    if (!$calendario->save()) {
                        $this->warnings[] = "Riga {$rowNum}: Errore creazione calendario: " . json_encode($calendario->errors);
                        continue;
                    }

                    $this->log[] = "Lezione creata: {$data} {$oraInizio}-{$oraFine} ({$ore}h) - {$modulo->titolo} - {$docente->cognome} {$docente->nome}";
                    $importati++;
                }

                $transaction->commit();
                @unlink($tempPath);

                $this->log[] = "Import completato: {$importati} lezioni create, {$saltati} saltate (duplicate)";

                return $this->asJson([
                    'success' => true,
                    'mode' => 'import',
                    'log' => $this->log,
                    'warnings' => $this->warnings,
                    'stats' => [
                        'nuove' => $importati,
                        'duplicate' => $saltati,
                    ],
                ]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                @unlink($tempPath);
                throw $e;
            }

        } catch (\Throwable $e) {
            @unlink($tempPath);
            Yii::error('Import calendari error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => 'Errore durante l\'import: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Legge righe da un foglio Excel.
     * Header da riga 1, dati da riga 2+.
     * Salta righe vuote e la riga esempio (riga 2 con sfondo grigio).
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return array Array di righe associative [header => valore]
     */
    private function leggiRigheFoglio($sheet)
    {
        $righe = [];
        $headers = [];

        foreach ($sheet->getRowIterator(1) as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if (is_object($value)) {
                    $value = $value->__toString();
                } elseif (is_array($value)) {
                    $value = '';
                } elseif ($value === null) {
                    $value = '';
                } else {
                    $value = (string)$value;
                }
                $cells[] = trim($value);
            }

            if ($rowIndex === 1) {
                // Header: rimuovi asterischi
                $headers = array_map(function ($h) {
                    return trim(str_replace('*', '', $h));
                }, $cells);
                continue;
            }

            // Salta riga 2 (esempio) - controlla se contiene testo indicativo di esempio
            if ($rowIndex === 2) {
                $lastCell = end($cells);
                if (stripos($lastCell, 'esempio') !== false || stripos($lastCell, 'cancellare') !== false) {
                    continue;
                }
            }

            // Salta righe completamente vuote
            $nonEmpty = array_filter($cells, function ($c) { return $c !== ''; });
            if (empty($nonEmpty)) {
                continue;
            }

            // Assicurati che cells e headers abbiano la stessa lunghezza
            while (count($cells) < count($headers)) {
                $cells[] = '';
            }
            $cells = array_slice($cells, 0, count($headers));

            $righe[] = array_combine($headers, $cells);
        }

        return $righe;
    }

    /**
     * Normalizza formato orario da Excel.
     * Gestisce: decimale Excel (0.375 = 09:00), HH:MM, HH:MM:SS
     *
     * @param mixed $orario Valore orario dal file Excel
     * @return string|null Orario in formato HH:MM o null se non valido
     */
    private function normalizzaOrario($orario)
    {
        if (empty($orario)) {
            return null;
        }

        $orario = trim((string)$orario);

        // Formato HH:MM:SS
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $orario, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }

        // Formato HH:MM
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $orario, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }

        // Formato decimale Excel (es: 0.375 = 09:00, 0.75 = 18:00)
        if (is_numeric($orario)) {
            $decimal = (float)$orario;
            if ($decimal >= 0 && $decimal < 1) {
                $totalMinutes = round($decimal * 24 * 60);
                $hours = (int)($totalMinutes / 60);
                $minutes = (int)($totalMinutes % 60);
                return sprintf('%02d:%02d', $hours, $minutes);
            }
            // Potrebbe essere un numero intero tipo 900 per 09:00 o 1300 per 13:00
            if ($decimal >= 100 && $decimal <= 2359) {
                $hours = (int)($decimal / 100);
                $minutes = (int)($decimal % 100);
                if ($hours < 24 && $minutes < 60) {
                    return sprintf('%02d:%02d', $hours, $minutes);
                }
            }
        }

        // Formato H.MM (es: 9.00 per 09:00)
        if (preg_match('/^(\d{1,2})\.(\d{2})$/', $orario, $m)) {
            $h = (int)$m[1];
            $min = (int)$m[2];
            if ($h < 24 && $min < 60) {
                return sprintf('%02d:%02d', $h, $min);
            }
        }

        return null;
    }
}
