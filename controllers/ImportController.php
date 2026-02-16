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
                ],
            ],
        ];
    }

    /**
     * Disabilita CSRF per upload (gestito via AJAX)
     */
    public function beforeAction($action)
    {
        if ($action->id === 'upload') {
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
}
