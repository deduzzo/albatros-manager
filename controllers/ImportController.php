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
use app\models\Allievo;
use app\models\Iscrizione;
use app\models\PresenzaAllievo;
use app\models\BorsaStudio;
use app\models\RendicontazioneOre;
use app\models\MovimentoSpesa;
use app\models\TrancheFinanziamento;
use app\models\VoceSpesa;

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
                    'upload-studenti' => ['POST'],
                    'upload-presenze' => ['POST'],
                    'upload-borse' => ['POST'],
                    'upload-rendicontazione' => ['POST'],
                    'upload-movimenti' => ['POST'],
                    'upload-tranche' => ['POST'],
                    'upload-pagamenti' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Disabilita CSRF per upload (gestito via AJAX)
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['upload', 'upload-assegnazioni', 'upload-calendari', 'upload-studenti', 'upload-presenze', 'upload-borse', 'upload-rendicontazione', 'upload-movimenti', 'upload-tranche', 'upload-pagamenti'])) {
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
     * Download template XLSX generato programmaticamente con dropdown e istruzioni chiare
     */
    public function actionDownloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // --- Foglio ISTRUZIONI ---
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');

        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT DATI - ALBATROS MANAGER');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $istruzioni->setCellValue('A3', 'Questo template permette di importare un progetto completo con moduli, docenti e assegnazioni.');
        $istruzioni->setCellValue('A5', 'ISTRUZIONI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', '1. Compilare i 3 fogli dati: INFO PROGETTO, MODULI, DOCENTI & ESITI');
        $istruzioni->setCellValue('A7', '2. I campi con * sono obbligatori');
        $istruzioni->setCellValue('A8', '3. Le date devono essere in formato AAAA-MM-GG (es: 2025-03-15)');
        $istruzioni->setCellValue('A9', '4. I codici modulo devono essere univoci e corrispondere tra i fogli MODULI e DOCENTI & ESITI');
        $istruzioni->setCellValue('A10', '5. Se ASSEGNATARIO viene rinunciato, aggiungere (Rinuncia) dopo il nome');
        $istruzioni->setCellValue('A11', '6. Più docenti per stesso modulo: ripetere CODICE_MODULO in ogni riga');

        $istruzioni->setCellValue('A13', 'VALORI AMMESSI PER CAMPI A SCELTA MULTIPLA (hanno menu a tendina nei fogli):');
        $istruzioni->getStyle('A13')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF206BC4'));

        $istruzioni->setCellValue('A14', 'FONTE_FINANZIAMENTO (foglio INFO PROGETTO):');
        $istruzioni->getStyle('A14')->getFont()->setBold(true);
        $istruzioni->setCellValue('A15', '   FSE = Fondo Sociale Europeo');
        $istruzioni->setCellValue('A16', '   PNRR = Piano Nazionale di Ripresa e Resilienza (default)');
        $istruzioni->setCellValue('A17', '   MIM = Ministero dell\'Istruzione e del Merito');

        $istruzioni->setCellValue('A19', 'PROVENIENZA (foglio DOCENTI & ESITI):');
        $istruzioni->getStyle('A19')->getFont()->setBold(true);
        $istruzioni->setCellValue('A20', '   TP = Terza Parte / Professionista (compenso default: €60/h)');
        $istruzioni->setCellValue('A21', '   RU = Ricercatore Universitario (compenso default: €70/h)');

        $istruzioni->setCellValue('A23', 'TIPO_CONTRATTO (foglio DOCENTI & ESITI):');
        $istruzioni->getStyle('A23')->getFont()->setBold(true);
        $istruzioni->setCellValue('A24', '   cococo = Contratto di Collaborazione Coordinata e Continuativa (default)');
        $istruzioni->setCellValue('A25', '   partita_iva = Prestazione Professionale con Partita IVA');
        $istruzioni->setCellValue('A26', '   occasionale = Lavoro Autonomo Occasionale');

        $istruzioni->setCellValue('A28', 'ESITO CTS (foglio DOCENTI & ESITI - determinato dalla colonna compilata):');
        $istruzioni->getStyle('A28')->getFont()->setBold(true);
        $istruzioni->setCellValue('A29', '   Colonna ASSEGNATARIO compilata → esito "assegnato" (vincitore del bando)');
        $istruzioni->setCellValue('A30', '   Colonna IDONEI compilata → esito "idoneo" (in long list, disponibile per subentro)');
        $istruzioni->setCellValue('A31', '   Colonna NON_IDONEI compilata → esito "non_idoneo" (formato: Nome Cognome (Motivazione))');

        $istruzioni->getColumnDimension('A')->setWidth(90);

        // --- Foglio INFO PROGETTO ---
        $infoProg = $spreadsheet->createSheet();
        $infoProg->setTitle('INFO PROGETTO');

        $campi = [
            ['CODICE_PROGETTO*', '', 'Codice univoco del progetto (es: AGRIFOOD-AVV2-ME)'],
            ['TITOLO_PROGETTO*', '', 'Titolo esteso del progetto'],
            ['FONTE_FINANZIAMENTO*', 'PNRR', 'FSE, PNRR o MIM (menu a tendina)'],
            ['BUDGET_TOTALE', '', 'Budget in euro (es: 150000)'],
            ['DATA_INIZIO', '', 'Formato AAAA-MM-GG'],
            ['DATA_FINE', '', 'Formato AAAA-MM-GG'],
            ['NUMERO_STUDENTI', '20', 'Numero massimo studenti (default: 20)'],
            ['CODICE_AVVISO*', '', 'Codice del bando (es: AVV-001)'],
            ['TITOLO_AVVISO*', '', 'Titolo dell\'avviso/bando'],
            ['DATA_PUBBLICAZIONE', '', 'Data pubblicazione bando (AAAA-MM-GG)'],
            ['DATA_SCADENZA', '', 'Data scadenza bando (AAAA-MM-GG)'],
            ['SEDE_NOME*', '', 'Nome della sede (es: IIS Ferraris)'],
            ['SEDE_INDIRIZZO', '', 'Indirizzo completo'],
            ['SEDE_CITTA*', '', 'Città della sede'],
            ['SEDE_PROVINCIA*', '', 'Sigla provincia (es: ME, PA, CT)'],
        ];

        // Header
        $infoProg->setCellValue('A1', 'CAMPO');
        $infoProg->setCellValue('B1', 'VALORE');
        $infoProg->setCellValue('C1', 'DESCRIZIONE');
        foreach (['A', 'B', 'C'] as $col) {
            $infoProg->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '206BC4'],
                ],
            ]);
        }

        foreach ($campi as $idx => $campo) {
            $row = $idx + 2;
            $infoProg->setCellValue('A' . $row, $campo[0]);
            $infoProg->setCellValue('B' . $row, $campo[1]);
            $infoProg->setCellValue('C' . $row, $campo[2]);
            $infoProg->getStyle('A' . $row)->getFont()->setBold(true);
            $infoProg->getStyle('C' . $row)->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF666666'));
        }

        // Dropdown per FONTE_FINANZIAMENTO (cella B4, riga di FONTE_FINANZIAMENTO)
        $validationFonte = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationFonte->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationFonte->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationFonte->setAllowBlank(false);
        $validationFonte->setShowDropDown(true);
        $validationFonte->setFormula1('"FSE,PNRR,MIM"');
        $validationFonte->setError('Valori ammessi: FSE (Fondo Sociale Europeo), PNRR, MIM (Ministero Istruzione)');
        $validationFonte->setPromptTitle('Fonte Finanziamento');
        $validationFonte->setPrompt('FSE = Fondo Sociale Europeo, PNRR = Piano Nazionale Ripresa e Resilienza, MIM = Ministero Istruzione e Merito');
        $validationFonte->setShowInputMessage(true);
        $validationFonte->setShowErrorMessage(true);
        $infoProg->getCell('B4')->setDataValidation($validationFonte);

        $infoProg->getColumnDimension('A')->setWidth(25);
        $infoProg->getColumnDimension('B')->setWidth(35);
        $infoProg->getColumnDimension('C')->setWidth(50);

        // --- Foglio MODULI ---
        $moduli = $spreadsheet->createSheet();
        $moduli->setTitle('MODULI');

        $headersModuli = [
            'A' => 'CODICE*',
            'B' => 'TITOLO*',
            'C' => 'DESCRIZIONE',
            'D' => 'FILIERA',
            'E' => 'ORE_TOTALI*',
            'F' => 'COMPENSO_ORARIO*',
            'G' => 'DATA_INIZIO',
            'H' => 'DATA_FINE',
            'I' => 'MAX_ALLIEVI',
        ];

        foreach ($headersModuli as $col => $header) {
            $moduli->setCellValue($col . '1', $header);
            $moduli->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '206BC4'],
                ],
            ]);
            $moduli->getColumnDimension($col)->setAutoSize(true);
        }

        // Riga esempio
        $esempioMod = ['MOD-001', 'Produzione Olivicola', 'Tecniche di produzione', 'olivicola', '20', '60', '2025-02-01', '2025-06-30', '20'];
        $colIdx = 0;
        foreach (array_keys($headersModuli) as $col) {
            $moduli->setCellValue($col . '2', $esempioMod[$colIdx]);
            $moduli->getStyle($col . '2')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
            ]);
            $colIdx++;
        }

        // --- Foglio DOCENTI & ESITI ---
        $docenti = $spreadsheet->createSheet();
        $docenti->setTitle('DOCENTI & ESITI');

        $headersDoc = [
            'A' => 'CODICE_MODULO*',
            'B' => 'ASSEGNATARIO',
            'C' => 'IDONEI',
            'D' => 'NON_IDONEI',
            'E' => 'PROVENIENZA*',
            'F' => 'TIPO_CONTRATTO',
            'G' => 'ORE_ASSEGNATE*',
            'H' => 'COMPENSO_ORARIO*',
            'I' => 'DATA_ESITI_CTS',
            'J' => 'NOTE',
        ];

        foreach ($headersDoc as $col => $header) {
            $docenti->setCellValue($col . '1', $header);
            $docenti->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '206BC4'],
                ],
            ]);
            $docenti->getColumnDimension($col)->setAutoSize(true);
        }

        // Riga esempio
        $esempioDoc = ['MOD-001', 'Rossi Mario', '', '', 'TP', 'cococo', '20', '60', '2025-01-15', 'Esempio - cancellare questa riga'];
        $colIdx = 0;
        foreach (array_keys($headersDoc) as $col) {
            $docenti->setCellValue($col . '2', $esempioDoc[$colIdx]);
            $docenti->getStyle($col . '2')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
            ]);
            $colIdx++;
        }

        // Dropdown per PROVENIENZA (colonna E, righe 3-200)
        $validationProv = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationProv->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationProv->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationProv->setAllowBlank(true);
        $validationProv->setShowDropDown(true);
        $validationProv->setFormula1('"TP,RU"');
        $validationProv->setError('Valori ammessi: TP (Professionista €60/h) o RU (Ricercatore €70/h)');
        $validationProv->setPromptTitle('Provenienza');
        $validationProv->setPrompt('TP = Professionista (€60/h), RU = Ricercatore (€70/h)');
        $validationProv->setShowInputMessage(true);
        $validationProv->setShowErrorMessage(true);

        // Dropdown per TIPO_CONTRATTO (colonna F, righe 3-200)
        $validationTipoC = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationTipoC->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationTipoC->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationTipoC->setAllowBlank(true);
        $validationTipoC->setShowDropDown(true);
        $validationTipoC->setFormula1('"cococo,partita_iva,occasionale"');
        $validationTipoC->setError('Valori ammessi: cococo, partita_iva, occasionale');
        $validationTipoC->setPromptTitle('Tipo Contratto');
        $validationTipoC->setPrompt('cococo = Co.Co.Co. (default), partita_iva = Prest. Professionale, occasionale = Autonomo Occasionale');
        $validationTipoC->setShowInputMessage(true);
        $validationTipoC->setShowErrorMessage(true);

        for ($row = 3; $row <= 200; $row++) {
            $docenti->getCell('E' . $row)->setDataValidation(clone $validationProv);
            $docenti->getCell('F' . $row)->setDataValidation(clone $validationTipoC);
        }

        // Seleziona il primo foglio
        $spreadsheet->setActiveSheetIndex(0);

        // Genera file
        $tempPath = Yii::getAlias('@runtime') . '/template_import_dati_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_ALBATROS.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) {
                @unlink($tempPath);
            });
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
        $istruzioni->setCellValue('A12', '7. TIPO_CONTRATTO: cococo (default), partita_iva, occasionale');
        $istruzioni->setCellValue('A13', '8. La riga 2 in grigio è un esempio e verrà ignorata dall\'import');
        $istruzioni->setCellValue('A15', 'CAMPI:');
        $istruzioni->getStyle('A15')->getFont()->setBold(true);
        $istruzioni->setCellValue('A16', 'CODICE_MODULO* - Codice del modulo esistente');
        $istruzioni->setCellValue('A17', 'COGNOME* - Cognome del docente');
        $istruzioni->setCellValue('A18', 'NOME* - Nome del docente');
        $istruzioni->setCellValue('A19', 'CODICE_FISCALE - CF del docente (se vuoto verrà generato automaticamente)');
        $istruzioni->setCellValue('A20', 'PROVENIENZA - TP o RU (default: TP). TP=Professionista €60/h, RU=Ricercatore €70/h');
        $istruzioni->setCellValue('A21', 'COMPENSO_ORARIO - Compenso orario in euro (default: da modulo)');
        $istruzioni->setCellValue('A22', 'ORE_ASSEGNATE - Ore assegnate (default: ore_totali del modulo)');
        $istruzioni->setCellValue('A23', 'TIPO_CONTRATTO - Tipo contratto (default: cococo). Valori: cococo, partita_iva, occasionale');
        $istruzioni->setCellValue('A24', 'DATA_ASSEGNAZIONE - Data assegnazione (formato YYYY-MM-DD)');
        $istruzioni->setCellValue('A25', 'NOTE - Note aggiuntive');

        $istruzioni->setCellValue('A27', 'VALORI AMMESSI PER CAMPI A SCELTA MULTIPLA:');
        $istruzioni->getStyle('A27')->getFont()->setBold(true);
        $istruzioni->setCellValue('A28', 'PROVENIENZA: TP (Terza Parte/Professionista, compenso default €60/h) | RU (Ricercatore Universitario, compenso default €70/h)');
        $istruzioni->setCellValue('A29', 'TIPO_CONTRATTO: cococo (Co.Co.Co., default) | partita_iva (Prestazione Professionale) | occasionale (Autonomo Occasionale)');

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

        // Data validation dropdown per PROVENIENZA (colonna E, righe 3-200)
        $validationProvenienza = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationProvenienza->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationProvenienza->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationProvenienza->setAllowBlank(true);
        $validationProvenienza->setShowDropDown(true);
        $validationProvenienza->setFormula1('"TP,RU"');
        $validationProvenienza->setError('Valori ammessi: TP (Professionista €60/h) o RU (Ricercatore €70/h)');
        $validationProvenienza->setPromptTitle('Provenienza');
        $validationProvenienza->setPrompt('TP = Professionista (€60/h), RU = Ricercatore (€70/h)');
        $validationProvenienza->setShowInputMessage(true);
        $validationProvenienza->setShowErrorMessage(true);
        for ($row = 3; $row <= 200; $row++) {
            $assegnazioni->getCell('E' . $row)->setDataValidation(clone $validationProvenienza);
        }

        // Data validation dropdown per TIPO_CONTRATTO (colonna H, righe 3-200)
        $validationTipo = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationTipo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationTipo->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationTipo->setAllowBlank(true);
        $validationTipo->setShowDropDown(true);
        $validationTipo->setFormula1('"cococo,partita_iva,occasionale"');
        $validationTipo->setError('Valori ammessi: cococo, partita_iva, occasionale');
        $validationTipo->setPromptTitle('Tipo Contratto');
        $validationTipo->setPrompt('cococo = Co.Co.Co. (default), partita_iva = Prest. Professionale, occasionale = Autonomo Occasionale');
        $validationTipo->setShowInputMessage(true);
        $validationTipo->setShowErrorMessage(true);
        for ($row = 3; $row <= 200; $row++) {
            $assegnazioni->getCell('H' . $row)->setDataValidation(clone $validationTipo);
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
        $istruzioni->setCellValue('A12', '7. STATUS: programmata (default), completata, annullata - menu a tendina nel foglio CALENDARIO');
        $istruzioni->setCellValue('A13', '8. La riga 2 in grigio è un esempio e verrà ignorata dall\'import');

        $istruzioni->setCellValue('A15', 'VALORI AMMESSI PER CAMPI A SCELTA MULTIPLA:');
        $istruzioni->getStyle('A15')->getFont()->setBold(true);
        $istruzioni->setCellValue('A16', 'STATUS: programmata (lezione pianificata, default) | completata (lezione svolta) | annullata (lezione annullata)');

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

        // Data validation dropdown per STATUS (colonna I, righe 3-500)
        $validationStatus = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $validationStatus->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationStatus->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validationStatus->setAllowBlank(true);
        $validationStatus->setShowDropDown(true);
        $validationStatus->setFormula1('"programmata,completata,annullata"');
        $validationStatus->setError('Valori ammessi: programmata, completata, annullata');
        $validationStatus->setPromptTitle('Status Lezione');
        $validationStatus->setPrompt('programmata = pianificata (default), completata = svolta, annullata = cancellata');
        $validationStatus->setShowInputMessage(true);
        $validationStatus->setShowErrorMessage(true);
        for ($row = 3; $row <= 500; $row++) {
            $calendario->getCell('I' . $row)->setDataValidation(clone $validationStatus);
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

    // ==========================================================
    // IMPORT 4: STUDENTI + ISCRIZIONI
    // ==========================================================

    /**
     * Download template studenti XLSX generato programmaticamente
     */
    public function actionDownloadTemplateStudenti()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT STUDENTI');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Questo template permette di importare studenti (allievi) e le relative iscrizioni ai moduli.');
        $istruzioni->setCellValue('A5', 'ISTRUZIONI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', '1. Compilare il foglio "STUDENTI" con i dati richiesti');
        $istruzioni->setCellValue('A7', '2. I campi con * sono obbligatori');
        $istruzioni->setCellValue('A8', '3. CF_STUDENTE deve essere un codice fiscale valido di 16 caratteri');
        $istruzioni->setCellValue('A9', '4. Se lo studente esiste già (stesso CF), i dati anagrafici verranno aggiornati');
        $istruzioni->setCellValue('A10', '5. CODICE_MODULO è opzionale: se fornito, verrà creata un\'iscrizione al modulo');
        $istruzioni->setCellValue('A11', '6. La riga 2 in grigio è un esempio e verrà ignorata dall\'import');
        $istruzioni->setCellValue('A13', 'VALORI AMMESSI PER CAMPI A SCELTA MULTIPLA:');
        $istruzioni->getStyle('A13')->getFont()->setBold(true);
        $istruzioni->setCellValue('A14', 'TITOLO_STUDIO: licenza_media | diploma | laurea_triennale | laurea_magistrale | master | dottorato');
        $istruzioni->setCellValue('A15', 'STATO_OCCUPAZIONALE: occupato | disoccupato | inoccupato | studente | pensionato');
        $istruzioni->setCellValue('A16', 'STATUS_ISCRIZIONE: candidato (default) | ammesso | frequentante | completato');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $studenti = $spreadsheet->createSheet();
        $studenti->setTitle('STUDENTI');
        $headers = [
            'A' => 'CF_STUDENTE*', 'B' => 'COGNOME*', 'C' => 'NOME*', 'D' => 'EMAIL',
            'E' => 'TELEFONO', 'F' => 'DATA_NASCITA', 'G' => 'LUOGO_NASCITA', 'H' => 'INDIRIZZO',
            'I' => 'CAP', 'J' => 'CITTA', 'K' => 'PROVINCIA', 'L' => 'TITOLO_STUDIO',
            'M' => 'STATO_OCCUPAZIONALE', 'N' => 'CODICE_MODULO', 'O' => 'STATUS_ISCRIZIONE',
        ];
        foreach ($headers as $col => $header) {
            $studenti->setCellValue($col . '1', $header);
            $studenti->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']],
            ]);
            $studenti->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['RSSMRA00A01H501Z', 'Rossi', 'Maria', 'maria.rossi@email.it', '3331234567', '2000-01-01', 'Roma', 'Via Roma 1', '00100', 'Roma', 'RM', 'diploma', 'studente', 'MOD-001', 'candidato'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $studenti->setCellValue($col . '2', $esempio[$colIdx]);
            $studenti->getStyle($col . '2')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
            ]);
            $colIdx++;
        }

        $vTitolo = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vTitolo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"licenza_media,diploma,laurea_triennale,laurea_magistrale,master,dottorato"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Titolo di Studio')->setPrompt('Selezionare il titolo di studio');
        $vStato = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vStato->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"occupato,disoccupato,inoccupato,studente,pensionato"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Stato Occupazionale')->setPrompt('Selezionare lo stato occupazionale');
        $vStatus = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vStatus->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"candidato,ammesso,frequentante,completato"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Status Iscrizione')->setPrompt('candidato (default), ammesso, frequentante, completato');
        for ($row = 3; $row <= 500; $row++) {
            $studenti->getCell('L' . $row)->setDataValidation(clone $vTitolo);
            $studenti->getCell('M' . $row)->setDataValidation(clone $vStato);
            $studenti->getCell('O' . $row)->setDataValidation(clone $vStatus);
        }

        $tempPath = Yii::getAlias('@runtime') . '/template_studenti_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_STUDENTI.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadStudenti()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_studenti_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('STUDENTI');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "STUDENTI" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati trovata']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = [];
            $stats = ['nuovi_studenti' => 0, 'aggiornati' => 0, 'iscrizioni_create' => 0, 'errori' => 0];
            $validTitoli = ['licenza_media', 'diploma', 'laurea_triennale', 'laurea_magistrale', 'master', 'dottorato'];
            $validStatiOccup = ['occupato', 'disoccupato', 'inoccupato', 'studente', 'pensionato'];
            $validStatusIscr = ['candidato', 'ammesso', 'frequentante', 'completato'];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $cf = strtoupper(trim($riga['CF_STUDENTE'] ?? ''));
                $cognome = trim($riga['COGNOME'] ?? '');
                $nome = trim($riga['NOME'] ?? '');
                $rowPreview = ['riga' => $rowNum, 'cf_studente' => $cf, 'cognome' => $cognome, 'nome' => $nome, 'codice_modulo' => $riga['CODICE_MODULO'] ?? '', 'azioni' => [], 'stato' => 'ok'];

                if (empty($cf) || strlen($cf) !== 16 || empty($cognome) || empty($nome)) {
                    $this->errors[] = "Riga {$rowNum}: CF_STUDENTE (16 car.), COGNOME e NOME obbligatori";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $titoloStudio = strtolower(trim($riga['TITOLO_STUDIO'] ?? ''));
                if (!empty($titoloStudio) && !in_array($titoloStudio, $validTitoli)) {
                    $this->errors[] = "Riga {$rowNum}: TITOLO_STUDIO '{$titoloStudio}' non valido";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $statoOccup = strtolower(trim($riga['STATO_OCCUPAZIONALE'] ?? ''));
                if (!empty($statoOccup) && !in_array($statoOccup, $validStatiOccup)) {
                    $this->errors[] = "Riga {$rowNum}: STATO_OCCUPAZIONALE '{$statoOccup}' non valido";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }

                $allievoDB = Allievo::findOne(['codice_fiscale' => $cf]);
                if ($allievoDB) { $rowPreview['azioni'][] = "Studente esistente - verrà aggiornato"; $stats['aggiornati']++; }
                else { $rowPreview['azioni'][] = "Nuovo studente: {$cognome} {$nome}"; $stats['nuovi_studenti']++; }

                $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                if (!empty($codiceModulo)) {
                    $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                    if (!$modulo) {
                        $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato";
                        $rowPreview['stato'] = 'errore'; $stats['errori']++;
                        if ($allievoDB) $stats['aggiornati']--; else $stats['nuovi_studenti']--;
                        $preview[] = $rowPreview; continue;
                    }
                    $iscrizioneEsistente = Iscrizione::findOne(['modulo_id' => $modulo->id, 'allievo_codice_fiscale' => $cf]);
                    if ($iscrizioneEsistente) {
                        $rowPreview['azioni'][] = "Iscrizione al modulo già esistente - saltata";
                    } else {
                        $statusIscr = strtolower(trim($riga['STATUS_ISCRIZIONE'] ?? 'candidato'));
                        if (!in_array($statusIscr, $validStatusIscr)) $statusIscr = 'candidato';
                        $rowPreview['azioni'][] = "Nuova iscrizione: {$modulo->titolo} (status: {$statusIscr})";
                        $stats['iscrizioni_create']++;
                    }
                }
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $nuovi = 0; $aggiornati = 0; $iscrizioni = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $cf = strtoupper(trim($riga['CF_STUDENTE'] ?? '')); $cognome = trim($riga['COGNOME'] ?? ''); $nome = trim($riga['NOME'] ?? '');
                    if (empty($cf) || strlen($cf) !== 16 || empty($cognome) || empty($nome)) continue;

                    $allievo = Allievo::findOne(['codice_fiscale' => $cf]);
                    $isNew = !$allievo;
                    if ($isNew) { $allievo = new Allievo(); $allievo->codice_fiscale = $cf; }
                    $allievo->cognome = $cognome; $allievo->nome = $nome;
                    if (!empty(trim($riga['EMAIL'] ?? ''))) $allievo->email = trim($riga['EMAIL']);
                    if (!empty(trim($riga['TELEFONO'] ?? ''))) $allievo->telefono = trim($riga['TELEFONO']);
                    $dataNascita = $riga['DATA_NASCITA'] ?? '';
                    if (!empty($dataNascita)) {
                        if (is_numeric($dataNascita)) $dataNascita = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$dataNascita)->format('Y-m-d');
                        $allievo->data_nascita = $dataNascita;
                    }
                    if (!empty(trim($riga['LUOGO_NASCITA'] ?? ''))) $allievo->luogo_nascita = trim($riga['LUOGO_NASCITA']);
                    if (!empty(trim($riga['INDIRIZZO'] ?? ''))) $allievo->indirizzo = trim($riga['INDIRIZZO']);
                    if (!empty(trim($riga['CAP'] ?? ''))) $allievo->cap = trim($riga['CAP']);
                    if (!empty(trim($riga['CITTA'] ?? ''))) $allievo->citta = trim($riga['CITTA']);
                    if (!empty(trim($riga['PROVINCIA'] ?? ''))) $allievo->provincia = strtoupper(trim($riga['PROVINCIA']));
                    $titoloStudio = strtolower(trim($riga['TITOLO_STUDIO'] ?? ''));
                    if (!empty($titoloStudio) && in_array($titoloStudio, $validTitoli)) $allievo->titolo_studio = $titoloStudio;
                    $statoOccup = strtolower(trim($riga['STATO_OCCUPAZIONALE'] ?? ''));
                    if (!empty($statoOccup) && in_array($statoOccup, $validStatiOccup)) $allievo->stato_occupazionale = $statoOccup;
                    if (!$allievo->save()) { $this->warnings[] = "Riga {$rowNum}: Errore: " . json_encode($allievo->errors); continue; }
                    if ($isNew) { $this->log[] = "Studente creato: {$cognome} {$nome}"; $nuovi++; } else { $aggiornati++; }

                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                    if (!empty($codiceModulo)) {
                        $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                        if (!$modulo || Iscrizione::findOne(['modulo_id' => $modulo->id, 'allievo_codice_fiscale' => $cf])) continue;
                        $statusIscr = strtolower(trim($riga['STATUS_ISCRIZIONE'] ?? 'candidato'));
                        if (!in_array($statusIscr, $validStatusIscr)) $statusIscr = 'candidato';
                        $iscrizione = new Iscrizione(['modulo_id' => $modulo->id, 'avviso_id' => $modulo->avviso_id, 'allievo_codice_fiscale' => $cf, 'data_iscrizione' => date('Y-m-d'), 'status' => $statusIscr]);
                        if ($iscrizione->save()) { $this->log[] = "Iscrizione: {$cognome} {$nome} → {$modulo->titolo}"; $iscrizioni++; }
                    }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$nuovi} creati, {$aggiornati} aggiornati, {$iscrizioni} iscrizioni";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuovi_studenti' => $nuovi, 'aggiornati' => $aggiornati, 'iscrizioni_create' => $iscrizioni]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import studenti: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 5: PRESENZE ALLIEVI
    // ==========================================================

    public function actionDownloadTemplatePresenze()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT PRESENZE');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Questo template permette di importare le presenze degli studenti alle lezioni.');
        $istruzioni->setCellValue('A5', 'ISTRUZIONI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', '1. CODICE_MODULO deve corrispondere a un modulo esistente');
        $istruzioni->setCellValue('A7', '2. DATA_LEZIONE deve corrispondere a una lezione nel calendario');
        $istruzioni->setCellValue('A8', '3. CF_STUDENTE deve corrispondere a uno studente esistente');
        $istruzioni->setCellValue('A9', '4. Presenze duplicate (stesso studente + lezione) verranno saltate');
        $istruzioni->setCellValue('A11', 'VALORI AMMESSI:');
        $istruzioni->getStyle('A11')->getFont()->setBold(true);
        $istruzioni->setCellValue('A12', 'PRESENTE: SI (default) | NO');
        $istruzioni->setCellValue('A13', 'GIUSTIFICATO: SI | NO (default)');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $presenze = $spreadsheet->createSheet();
        $presenze->setTitle('PRESENZE');
        $headers = ['A' => 'CODICE_MODULO*', 'B' => 'DATA_LEZIONE*', 'C' => 'CF_STUDENTE*', 'D' => 'PRESENTE', 'E' => 'ORA_INGRESSO', 'F' => 'ORA_USCITA', 'G' => 'GIUSTIFICATO', 'H' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $presenze->setCellValue($col . '1', $header);
            $presenze->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $presenze->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['MOD-001', '2025-02-01', 'RSSMRA00A01H501Z', 'SI', '09:00', '13:00', 'NO', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $presenze->setCellValue($col . '2', $esempio[$colIdx]);
            $presenze->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vP = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vP->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"SI,NO"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Presente')->setPrompt('SI = presente, NO = assente');
        $vG = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vG->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"SI,NO"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Giustificato')->setPrompt('SI = giustificata, NO = ingiustificata');
        for ($row = 3; $row <= 1000; $row++) {
            $presenze->getCell('D' . $row)->setDataValidation(clone $vP);
            $presenze->getCell('G' . $row)->setDataValidation(clone $vG);
        }
        $tempPath = Yii::getAlias('@runtime') . '/template_presenze_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_PRESENZE.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadPresenze()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_presenze_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('PRESENZE');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "PRESENZE" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuove_presenze' => 0, 'duplicate' => 0, 'errori' => 0];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $codiceModulo = trim($riga['CODICE_MODULO'] ?? '');
                $dataLezione = $riga['DATA_LEZIONE'] ?? '';
                $cfStudente = strtoupper(trim($riga['CF_STUDENTE'] ?? ''));
                $rowPreview = ['riga' => $rowNum, 'codice_modulo' => $codiceModulo, 'data_lezione' => $dataLezione, 'cf_studente' => $cfStudente, 'presente' => '', 'azione' => 'CREA'];

                if (empty($codiceModulo) || empty($dataLezione) || empty($cfStudente)) {
                    $this->errors[] = "Riga {$rowNum}: CODICE_MODULO, DATA_LEZIONE e CF_STUDENTE obbligatori";
                    $rowPreview['azione'] = 'ERRORE'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                if (is_numeric($dataLezione)) $dataLezione = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$dataLezione)->format('Y-m-d');
                $rowPreview['data_lezione'] = $dataLezione;

                $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                if (!$modulo) { $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato"; $rowPreview['azione'] = 'ERRORE'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $calendario = Calendario::findOne(['modulo_id' => $modulo->id, 'data' => $dataLezione]);
                if (!$calendario) { $this->errors[] = "Riga {$rowNum}: Nessuna lezione per modulo '{$codiceModulo}' in data {$dataLezione}"; $rowPreview['azione'] = 'ERRORE'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $allievo = Allievo::findOne(['codice_fiscale' => $cfStudente]);
                if (!$allievo) { $this->errors[] = "Riga {$rowNum}: Studente CF '{$cfStudente}' non trovato"; $rowPreview['azione'] = 'ERRORE'; $stats['errori']++; $preview[] = $rowPreview; continue; }

                $presenteVal = strtoupper(trim($riga['PRESENTE'] ?? '')); $presente = ($presenteVal !== 'NO');
                $rowPreview['presente'] = $presente ? 'SI' : 'NO';
                $rowPreview['studente_nome'] = $allievo->cognome . ' ' . $allievo->nome;

                $duplicata = PresenzaAllievo::findOne(['calendario_id' => $calendario->id, 'allievo_codice_fiscale' => $cfStudente]);
                if ($duplicata) { $rowPreview['azione'] = 'SALTA'; $stats['duplicate']++; }
                else { $stats['nuove_presenze']++; }
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0; $saltati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? ''); $cfStudente = strtoupper(trim($riga['CF_STUDENTE'] ?? ''));
                    $dataLezione = $riga['DATA_LEZIONE'] ?? '';
                    if (empty($codiceModulo) || empty($cfStudente) || empty($dataLezione)) continue;
                    if (is_numeric($dataLezione)) $dataLezione = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$dataLezione)->format('Y-m-d');
                    $modulo = Modulo::findOne(['codice' => $codiceModulo]); if (!$modulo) continue;
                    $calendario = Calendario::findOne(['modulo_id' => $modulo->id, 'data' => $dataLezione]); if (!$calendario) continue;
                    $allievo = Allievo::findOne(['codice_fiscale' => $cfStudente]); if (!$allievo) continue;
                    if (PresenzaAllievo::findOne(['calendario_id' => $calendario->id, 'allievo_codice_fiscale' => $cfStudente])) { $saltati++; continue; }

                    $presente = (strtoupper(trim($riga['PRESENTE'] ?? '')) !== 'NO');
                    $giustificato = (strtoupper(trim($riga['GIUSTIFICATO'] ?? '')) === 'SI');
                    $pa = new PresenzaAllievo(['calendario_id' => $calendario->id, 'allievo_codice_fiscale' => $cfStudente, 'presente' => $presente ? 1 : 0, 'giustificato' => $giustificato ? 1 : 0, 'ora_ingresso' => $this->normalizzaOrario($riga['ORA_INGRESSO'] ?? ''), 'ora_uscita' => $this->normalizzaOrario($riga['ORA_USCITA'] ?? ''), 'note' => trim($riga['NOTE'] ?? '')]);
                    if ($pa->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($pa->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} presenze create, {$saltati} duplicate saltate";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuove_presenze' => $importati, 'duplicate' => $saltati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import presenze: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 6: BORSE DI STUDIO
    // ==========================================================

    public function actionDownloadTemplateBorse()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT BORSE DI STUDIO');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Importa borse di studio per studenti iscritti ai progetti.');
        $istruzioni->setCellValue('A5', 'IMPORTI BASE PER TIPOLOGIA SEDE:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', 'in_sede = ' . number_format(BorsaStudio::IMPORTO_BASE_IN_SEDE, 2, ',', '.') . ' euro');
        $istruzioni->setCellValue('A7', 'pendolare = ' . number_format(BorsaStudio::IMPORTO_BASE_PENDOLARE, 2, ',', '.') . ' euro');
        $istruzioni->setCellValue('A8', 'fuori_sede = ' . number_format(BorsaStudio::IMPORTO_BASE_FUORI_SEDE, 2, ',', '.') . ' euro');
        $istruzioni->setCellValue('A10', 'L\'importo base viene calcolato automaticamente dalla tipologia sede.');
        $istruzioni->setCellValue('A11', 'VALORI AMMESSI: TIPOLOGIA_SEDE: in_sede | pendolare | fuori_sede');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $borse = $spreadsheet->createSheet();
        $borse->setTitle('BORSE_STUDIO');
        $headers = ['A' => 'CF_STUDENTE*', 'B' => 'CODICE_PROGETTO*', 'C' => 'ANNO_FORMATIVO*', 'D' => 'TIPOLOGIA_SEDE*', 'E' => 'ISEE_IMPORTO', 'F' => 'ISEE_PROTOCOLLO', 'G' => 'IBAN_STUDENTE', 'H' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $borse->setCellValue($col . '1', $header);
            $borse->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $borse->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['RSSMRA00A01H501Z', 'PRG-001', '2024/2025', 'pendolare', '15000', 'ISEE-2024-12345', 'IT60X0542811101000000123456', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $borse->setCellValue($col . '2', $esempio[$colIdx]);
            $borse->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vTip = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vTip->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(false)->setShowDropDown(true)->setFormula1('"in_sede,pendolare,fuori_sede"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Tipologia Sede');
        for ($row = 3; $row <= 200; $row++) { $borse->getCell('D' . $row)->setDataValidation(clone $vTip); }

        $tempPath = Yii::getAlias('@runtime') . '/template_borse_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_BORSE_STUDIO.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadBorse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_borse_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('BORSE_STUDIO');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "BORSE_STUDIO" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuove_borse' => 0, 'duplicate' => 0, 'errori' => 0];
            $validTipologie = [BorsaStudio::TIPOLOGIA_IN_SEDE, BorsaStudio::TIPOLOGIA_PENDOLARE, BorsaStudio::TIPOLOGIA_FUORI_SEDE];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $cfStudente = strtoupper(trim($riga['CF_STUDENTE'] ?? ''));
                $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? '');
                $annoFormativo = trim($riga['ANNO_FORMATIVO'] ?? '');
                $tipologiaSede = strtolower(trim($riga['TIPOLOGIA_SEDE'] ?? ''));
                $rowPreview = ['riga' => $rowNum, 'cf_studente' => $cfStudente, 'codice_progetto' => $codiceProgetto, 'anno_formativo' => $annoFormativo, 'tipologia_sede' => $tipologiaSede, 'azioni' => [], 'stato' => 'ok'];

                if (empty($cfStudente) || empty($codiceProgetto) || empty($annoFormativo) || !in_array($tipologiaSede, $validTipologie)) {
                    $this->errors[] = "Riga {$rowNum}: CF_STUDENTE, CODICE_PROGETTO, ANNO_FORMATIVO e TIPOLOGIA_SEDE (in_sede/pendolare/fuori_sede) obbligatori";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $allievo = Allievo::findOne(['codice_fiscale' => $cfStudente]);
                if (!$allievo) { $this->errors[] = "Riga {$rowNum}: Studente CF '{$cfStudente}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]);
                if (!$progetto) { $this->errors[] = "Riga {$rowNum}: Progetto '{$codiceProgetto}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $borsaEsistente = BorsaStudio::findOne(['allievo_codice_fiscale' => $cfStudente, 'progetto_id' => $progetto->id, 'anno_formativo' => $annoFormativo]);
                if ($borsaEsistente) { $rowPreview['stato'] = 'warning'; $rowPreview['azioni'][] = 'Borsa già esistente - saltata'; $stats['duplicate']++; $preview[] = $rowPreview; continue; }

                $importoBase = BorsaStudio::getImportoBase($tipologiaSede);
                $rowPreview['azioni'][] = "Nuova borsa: {$tipologiaSede}, €" . number_format($importoBase, 2, ',', '.');
                $stats['nuove_borse']++;
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $cfStudente = strtoupper(trim($riga['CF_STUDENTE'] ?? '')); $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? ''); $annoFormativo = trim($riga['ANNO_FORMATIVO'] ?? ''); $tipologiaSede = strtolower(trim($riga['TIPOLOGIA_SEDE'] ?? ''));
                    if (empty($cfStudente) || empty($codiceProgetto) || empty($annoFormativo) || empty($tipologiaSede)) continue;
                    $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]); if (!$progetto) continue;
                    if (BorsaStudio::findOne(['allievo_codice_fiscale' => $cfStudente, 'progetto_id' => $progetto->id, 'anno_formativo' => $annoFormativo])) continue;
                    $importoBase = BorsaStudio::getImportoBase($tipologiaSede);
                    $borsa = new BorsaStudio(['allievo_codice_fiscale' => $cfStudente, 'progetto_id' => $progetto->id, 'anno_formativo' => $annoFormativo, 'tipologia_sede' => $tipologiaSede, 'importo_base' => $importoBase, 'importo_finale' => $importoBase, 'status' => BorsaStudio::STATUS_RICHIESTA]);
                    $isee = trim($riga['ISEE_IMPORTO'] ?? ''); if ($isee !== '' && is_numeric($isee)) $borsa->isee_importo = (float)$isee;
                    if (!empty(trim($riga['ISEE_PROTOCOLLO'] ?? ''))) $borsa->isee_protocollo = trim($riga['ISEE_PROTOCOLLO']);
                    if (!empty(trim($riga['IBAN_STUDENTE'] ?? ''))) $borsa->iban_studente = trim($riga['IBAN_STUDENTE']);
                    if (!empty(trim($riga['NOTE'] ?? ''))) $borsa->note = trim($riga['NOTE']);
                    if ($borsa->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($borsa->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} borse create";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuove_borse' => $importati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import borse: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 7: RENDICONTAZIONE ORE
    // ==========================================================

    public function actionDownloadTemplateRendicontazione()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT RENDICONTAZIONE ORE');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Importa la rendicontazione ore dei docenti assegnati ai moduli.');
        $istruzioni->setCellValue('A5', 'VALORI AMMESSI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', 'STATUS: inserito (default) | approvato | rifiutato');
        $istruzioni->setCellValue('A7', 'ORE: minimo 0.25. Duplicati (stessa assegnazione+data) saltati.');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $rend = $spreadsheet->createSheet();
        $rend->setTitle('RENDICONTAZIONE');
        $headers = ['A' => 'CODICE_MODULO*', 'B' => 'CF_DOCENTE*', 'C' => 'DATA*', 'D' => 'ORE*', 'E' => 'ORA_INIZIO', 'F' => 'ORA_FINE', 'G' => 'STATUS', 'H' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $rend->setCellValue($col . '1', $header);
            $rend->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $rend->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['MOD-001', 'RSSMRA80A01H501Z', '2025-02-01', '4', '09:00', '13:00', 'inserito', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $rend->setCellValue($col . '2', $esempio[$colIdx]);
            $rend->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vS = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vS->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"inserito,approvato,rifiutato"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Status')->setPrompt('inserito (default), approvato, rifiutato');
        for ($row = 3; $row <= 500; $row++) { $rend->getCell('G' . $row)->setDataValidation(clone $vS); }

        $tempPath = Yii::getAlias('@runtime') . '/template_rendicontazione_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_RENDICONTAZIONE_ORE.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadRendicontazione()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_rendicontazione_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('RENDICONTAZIONE');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "RENDICONTAZIONE" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuove_ore' => 0, 'duplicate' => 0, 'errori' => 0];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $codiceModulo = trim($riga['CODICE_MODULO'] ?? ''); $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? ''));
                $data = trim($riga['DATA'] ?? ''); $ore = trim($riga['ORE'] ?? '');
                $rowPreview = ['riga' => $rowNum, 'codice_modulo' => $codiceModulo, 'cf_docente' => $cfDocente, 'data' => $data, 'ore' => $ore, 'azioni' => [], 'stato' => 'ok'];

                if (empty($codiceModulo) || empty($cfDocente) || empty($data) || !is_numeric($ore) || (float)$ore < 0.25) {
                    $this->errors[] = "Riga {$rowNum}: campi obbligatori mancanti o ORE non valido (min 0.25)";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                if (is_numeric($data)) $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$data)->format('Y-m-d');
                $rowPreview['data'] = $data;

                $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                if (!$modulo) { $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $docente = Docente::findOne(['codice_fiscale' => $cfDocente]);
                if (!$docente) { $this->errors[] = "Riga {$rowNum}: Docente CF '{$cfDocente}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $assegnazione = Assegnazione::findOne(['modulo_id' => $modulo->id, 'docente_codice_fiscale' => $cfDocente]);
                if (!$assegnazione) { $this->errors[] = "Riga {$rowNum}: Assegnazione non trovata per {$docente->cognome} {$docente->nome} al modulo {$codiceModulo}"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }

                $duplicato = RendicontazioneOre::findOne(['assegnazione_id' => $assegnazione->id, 'data' => $data]);
                if ($duplicato) { $rowPreview['stato'] = 'warning'; $rowPreview['azioni'][] = "Già esistente per data {$data} - saltata"; $stats['duplicate']++; }
                else { $rowPreview['azioni'][] = "{$ore}h il {$data}"; $stats['nuove_ore']++; }
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? ''); $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? '')); $data = trim($riga['DATA'] ?? ''); $ore = trim($riga['ORE'] ?? '');
                    if (empty($codiceModulo) || empty($cfDocente) || empty($data) || !is_numeric($ore)) continue;
                    if (is_numeric($data)) $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$data)->format('Y-m-d');
                    $modulo = Modulo::findOne(['codice' => $codiceModulo]); if (!$modulo) continue;
                    $assegnazione = Assegnazione::findOne(['modulo_id' => $modulo->id, 'docente_codice_fiscale' => $cfDocente]); if (!$assegnazione) continue;
                    if (RendicontazioneOre::findOne(['assegnazione_id' => $assegnazione->id, 'data' => $data])) continue;
                    $status = strtolower(trim($riga['STATUS'] ?? 'inserito'));
                    if (!in_array($status, [RendicontazioneOre::STATUS_INSERITO, RendicontazioneOre::STATUS_APPROVATO, RendicontazioneOre::STATUS_RIFIUTATO])) $status = RendicontazioneOre::STATUS_INSERITO;
                    $r = new RendicontazioneOre(['assegnazione_id' => $assegnazione->id, 'data' => $data, 'ore' => (float)$ore, 'status' => $status, 'note' => trim($riga['NOTE'] ?? '') ?: null]);
                    $oraInizio = $this->normalizzaOrario($riga['ORA_INIZIO'] ?? ''); if ($oraInizio) $r->ora_inizio = $oraInizio . ':00';
                    $oraFine = $this->normalizzaOrario($riga['ORA_FINE'] ?? ''); if ($oraFine) $r->ora_fine = $oraFine . ':00';
                    if ($r->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($r->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} rendicontazioni create";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuove_ore' => $importati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import rendicontazione: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 8: MOVIMENTI SPESA
    // ==========================================================

    public function actionDownloadTemplateMovimenti()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT MOVIMENTI SPESA');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Importa movimenti di spesa/entrata collegati ai progetti.');
        $istruzioni->setCellValue('A5', 'VALORI AMMESSI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', 'TIPO: spesa (default) | entrata');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $mov = $spreadsheet->createSheet();
        $mov->setTitle('MOVIMENTI');
        $headers = ['A' => 'CODICE_PROGETTO', 'B' => 'CODICE_VOCE_SPESA*', 'C' => 'DESCRIZIONE*', 'D' => 'IMPORTO*', 'E' => 'DATA_MOVIMENTO*', 'F' => 'TIPO', 'G' => 'NUMERO_DOCUMENTO', 'H' => 'FORNITORE', 'I' => 'DATA_DOCUMENTO', 'J' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $mov->setCellValue($col . '1', $header);
            $mov->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $mov->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['PRG-001', 'B2.1', 'Compenso docente', '1500.00', '2025-03-01', 'spesa', 'FT-2025/001', 'Mario Rossi', '2025-02-28', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $mov->setCellValue($col . '2', $esempio[$colIdx]);
            $mov->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vT = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vT->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"spesa,entrata"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Tipo')->setPrompt('spesa (default) o entrata');
        for ($row = 3; $row <= 500; $row++) { $mov->getCell('F' . $row)->setDataValidation(clone $vT); }

        $tempPath = Yii::getAlias('@runtime') . '/template_movimenti_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_MOVIMENTI.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadMovimenti()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_movimenti_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('MOVIMENTI');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "MOVIMENTI" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuovi_movimenti' => 0, 'errori' => 0];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $codiceVoce = trim($riga['CODICE_VOCE_SPESA'] ?? ''); $descrizione = trim($riga['DESCRIZIONE'] ?? '');
                $importo = trim($riga['IMPORTO'] ?? ''); $dataMovimento = trim($riga['DATA_MOVIMENTO'] ?? '');
                $rowPreview = ['riga' => $rowNum, 'descrizione' => $descrizione, 'importo' => $importo, 'azioni' => [], 'stato' => 'ok'];

                if (empty($codiceVoce) || empty($descrizione) || empty($importo) || empty($dataMovimento) || !is_numeric($importo)) {
                    $this->errors[] = "Riga {$rowNum}: campi obbligatori mancanti o importo non valido";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $voceSpesa = VoceSpesa::findOne(['codice' => $codiceVoce]);
                if (!$voceSpesa) { $this->errors[] = "Riga {$rowNum}: Voce di spesa '{$codiceVoce}' non trovata"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? '');
                if (!empty($codiceProgetto)) {
                    $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]);
                    if (!$progetto) { $this->errors[] = "Riga {$rowNum}: Progetto '{$codiceProgetto}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                }
                $tipo = strtolower(trim($riga['TIPO'] ?? 'spesa')); if (!in_array($tipo, ['spesa', 'entrata'])) $tipo = 'spesa';
                $rowPreview['azioni'][] = "{$tipo} €{$importo} - {$descrizione}";
                $stats['nuovi_movimenti']++;
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceVoce = trim($riga['CODICE_VOCE_SPESA'] ?? ''); $descrizione = trim($riga['DESCRIZIONE'] ?? ''); $importo = trim($riga['IMPORTO'] ?? ''); $dataMovimento = trim($riga['DATA_MOVIMENTO'] ?? '');
                    if (empty($codiceVoce) || empty($descrizione) || empty($importo) || empty($dataMovimento)) continue;
                    $voceSpesa = VoceSpesa::findOne(['codice' => $codiceVoce]); if (!$voceSpesa) continue;
                    $progettoId = null; $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? '');
                    if (!empty($codiceProgetto)) { $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]); if ($progetto) $progettoId = $progetto->id; }
                    $tipo = strtolower(trim($riga['TIPO'] ?? 'spesa')); if (!in_array($tipo, ['spesa', 'entrata'])) $tipo = 'spesa';
                    $m = new MovimentoSpesa(['progetto_id' => $progettoId, 'voce_spesa_id' => $voceSpesa->id, 'descrizione' => $descrizione, 'importo' => (float)$importo, 'data_movimento' => $dataMovimento, 'tipo' => $tipo, 'numero_documento' => trim($riga['NUMERO_DOCUMENTO'] ?? '') ?: null, 'fornitore' => trim($riga['FORNITORE'] ?? '') ?: null, 'data_documento' => trim($riga['DATA_DOCUMENTO'] ?? '') ?: null, 'note' => trim($riga['NOTE'] ?? '') ?: null]);
                    if ($m->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($m->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} movimenti creati";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuovi_movimenti' => $importati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import movimenti: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 9: TRANCHE FINANZIAMENTO
    // ==========================================================

    public function actionDownloadTemplateTranche()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT TRANCHE FINANZIAMENTO');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Importa le tranche di finanziamento per i progetti.');
        $istruzioni->setCellValue('A5', 'VALORI AMMESSI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', 'STATUS: prevista (default) | accreditata | parziale');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $tr = $spreadsheet->createSheet();
        $tr->setTitle('TRANCHE');
        $headers = ['A' => 'CODICE_PROGETTO*', 'B' => 'IMPORTO*', 'C' => 'DATA_PREVISTA', 'D' => 'DATA_ACCREDITO', 'E' => 'NUMERO_TRANCHE', 'F' => 'RIFERIMENTO', 'G' => 'STATUS', 'H' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $tr->setCellValue($col . '1', $header);
            $tr->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $tr->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['PRG-001', '50000.00', '2025-06-30', '2025-07-15', '1', 'Decreto 123/2025', 'accreditata', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $tr->setCellValue($col . '2', $esempio[$colIdx]);
            $tr->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vS = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vS->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"prevista,accreditata,parziale"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Status')->setPrompt('prevista (default), accreditata, parziale');
        for ($row = 3; $row <= 200; $row++) { $tr->getCell('G' . $row)->setDataValidation(clone $vS); }

        $tempPath = Yii::getAlias('@runtime') . '/template_tranche_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_TRANCHE.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadTranche()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_tranche_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('TRANCHE');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "TRANCHE" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuove_tranche' => 0, 'errori' => 0];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? ''); $importo = trim($riga['IMPORTO'] ?? '');
                $rowPreview = ['riga' => $rowNum, 'codice_progetto' => $codiceProgetto, 'importo' => $importo, 'azioni' => [], 'stato' => 'ok'];

                if (empty($codiceProgetto) || empty($importo) || !is_numeric($importo) || (float)$importo <= 0) {
                    $this->errors[] = "Riga {$rowNum}: CODICE_PROGETTO e IMPORTO (positivo) obbligatori";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]);
                if (!$progetto) { $this->errors[] = "Riga {$rowNum}: Progetto '{$codiceProgetto}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $status = strtolower(trim($riga['STATUS'] ?? 'prevista')); if (!in_array($status, ['prevista', 'accreditata', 'parziale'])) $status = 'prevista';
                $rowPreview['azioni'][] = "Tranche €{$importo} - {$status}";
                $stats['nuove_tranche']++;
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceProgetto = trim($riga['CODICE_PROGETTO'] ?? ''); $importo = trim($riga['IMPORTO'] ?? '');
                    if (empty($codiceProgetto) || empty($importo)) continue;
                    $progetto = Progetto::findOne(['codice_progetto' => $codiceProgetto]); if (!$progetto) continue;
                    $status = strtolower(trim($riga['STATUS'] ?? 'prevista')); if (!in_array($status, ['prevista', 'accreditata', 'parziale'])) $status = 'prevista';
                    $t = new TrancheFinanziamento(['progetto_id' => $progetto->id, 'importo' => (float)$importo, 'data_prevista' => trim($riga['DATA_PREVISTA'] ?? '') ?: null, 'data_accredito' => trim($riga['DATA_ACCREDITO'] ?? '') ?: null, 'numero_tranche' => !empty($riga['NUMERO_TRANCHE']) ? (int)$riga['NUMERO_TRANCHE'] : null, 'riferimento' => trim($riga['RIFERIMENTO'] ?? '') ?: null, 'status' => $status, 'note' => trim($riga['NOTE'] ?? '') ?: null]);
                    if ($t->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($t->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} tranche create";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuove_tranche' => $importati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import tranche: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }

    // ==========================================================
    // IMPORT 10: PAGAMENTI STORICI
    // ==========================================================

    public function actionDownloadTemplatePagamenti()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $istruzioni = $spreadsheet->getActiveSheet();
        $istruzioni->setTitle('ISTRUZIONI');
        $istruzioni->setCellValue('A1', 'TEMPLATE IMPORT PAGAMENTI');
        $istruzioni->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $istruzioni->setCellValue('A3', 'Importa pagamenti per contratti docente esistenti.');
        $istruzioni->setCellValue('A5', 'VALORI AMMESSI:');
        $istruzioni->getStyle('A5')->getFont()->setBold(true);
        $istruzioni->setCellValue('A6', 'TIPO: acconto | saldo (default) | rata | rimborso');
        $istruzioni->setCellValue('A7', 'STATUS: programmato | in_attesa | pagato (default) | annullato');
        $istruzioni->getColumnDimension('A')->setWidth(80);

        $pag = $spreadsheet->createSheet();
        $pag->setTitle('PAGAMENTI');
        $headers = ['A' => 'CODICE_MODULO*', 'B' => 'CF_DOCENTE*', 'C' => 'IMPORTO_LORDO*', 'D' => 'IMPORTO_NETTO*', 'E' => 'RITENUTE', 'F' => 'TIPO', 'G' => 'STATUS', 'H' => 'DATA_PAGAMENTO', 'I' => 'RIFERIMENTO_FATTURA', 'J' => 'NUMERO_MANDATO', 'K' => 'NOTE'];
        foreach ($headers as $col => $header) {
            $pag->setCellValue($col . '1', $header);
            $pag->getStyle($col . '1')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']]]);
            $pag->getColumnDimension($col)->setAutoSize(true);
        }
        $esempio = ['MOD-001', 'RSSMRA80A01H501Z', '2000.00', '1500.00', '500.00', 'saldo', 'pagato', '2025-03-15', 'FT-2025/042', 'M-2025/001', 'Esempio'];
        $colIdx = 0;
        foreach (array_keys($headers) as $col) {
            $pag->setCellValue($col . '2', $esempio[$colIdx]);
            $pag->getStyle($col . '2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '999999']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]]);
            $colIdx++;
        }
        $vTipo = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vTipo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"acconto,saldo,rata,rimborso"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Tipo')->setPrompt('acconto, saldo (default), rata, rimborso');
        $vStat = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
        $vStat->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING)->setAllowBlank(true)->setShowDropDown(true)->setFormula1('"programmato,in_attesa,pagato,annullato"')->setShowInputMessage(true)->setShowErrorMessage(true)->setPromptTitle('Status')->setPrompt('programmato, in_attesa, pagato (default), annullato');
        for ($row = 3; $row <= 500; $row++) { $pag->getCell('F' . $row)->setDataValidation(clone $vTipo); $pag->getCell('G' . $row)->setDataValidation(clone $vStat); }

        $tempPath = Yii::getAlias('@runtime') . '/template_pagamenti_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);
        return Yii::$app->response->sendFile($tempPath, 'TEMPLATE_IMPORT_PAGAMENTI.xlsx')
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempPath) { @unlink($tempPath); });
    }

    public function actionUploadPagamenti()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $uploadedFile = UploadedFile::getInstanceByName('excel_file');
        if (!$uploadedFile) return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        $tempPath = Yii::getAlias('@runtime') . '/import_pagamenti_' . time() . '_' . $uploadedFile->name;
        if (!$uploadedFile->saveAs($tempPath)) return $this->asJson(['success' => false, 'error' => 'Errore nel salvare il file']);
        $confirm = Yii::$app->request->post('confirm') === '1';

        try {
            $spreadsheet = IOFactory::load($tempPath);
            $sheet = $spreadsheet->getSheetByName('PAGAMENTI');
            if (!$sheet) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Foglio "PAGAMENTI" non trovato']); }
            $righe = $this->leggiRigheFoglio($sheet);
            if (empty($righe)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Nessuna riga di dati']); }

            $this->log = []; $this->errors = []; $this->warnings = [];
            $preview = []; $stats = ['nuovi_pagamenti' => 0, 'errori' => 0];

            foreach ($righe as $idx => $riga) {
                $rowNum = $idx + 3;
                $codiceModulo = trim($riga['CODICE_MODULO'] ?? ''); $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? ''));
                $importoLordo = trim($riga['IMPORTO_LORDO'] ?? ''); $importoNetto = trim($riga['IMPORTO_NETTO'] ?? '');
                $rowPreview = ['riga' => $rowNum, 'codice_modulo' => $codiceModulo, 'cf_docente' => $cfDocente, 'importo_lordo' => $importoLordo, 'azioni' => [], 'stato' => 'ok'];

                if (empty($codiceModulo) || empty($cfDocente) || !is_numeric($importoLordo) || !is_numeric($importoNetto)) {
                    $this->errors[] = "Riga {$rowNum}: CODICE_MODULO, CF_DOCENTE, IMPORTO_LORDO e IMPORTO_NETTO obbligatori (numerici)";
                    $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue;
                }
                $modulo = Modulo::findOne(['codice' => $codiceModulo]);
                if (!$modulo) { $this->errors[] = "Riga {$rowNum}: Modulo '{$codiceModulo}' non trovato"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $assegnazione = Assegnazione::findOne(['modulo_id' => $modulo->id, 'docente_codice_fiscale' => $cfDocente]);
                if (!$assegnazione) { $this->errors[] = "Riga {$rowNum}: Assegnazione non trovata per docente {$cfDocente} al modulo {$codiceModulo}"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }
                $contratto = Contratto::findOne(['assegnazione_id' => $assegnazione->id]);
                if (!$contratto) { $this->errors[] = "Riga {$rowNum}: Contratto non trovato per l'assegnazione"; $rowPreview['stato'] = 'errore'; $stats['errori']++; $preview[] = $rowPreview; continue; }

                $tipo = strtolower(trim($riga['TIPO'] ?? 'saldo')); if (!in_array($tipo, ['acconto', 'saldo', 'rata', 'rimborso'])) $tipo = 'saldo';
                $status = strtolower(trim($riga['STATUS'] ?? 'pagato')); if (!in_array($status, ['programmato', 'in_attesa', 'pagato', 'annullato'])) $status = 'pagato';
                $rowPreview['azioni'][] = "Pagamento: lordo €{$importoLordo}, netto €{$importoNetto}, {$tipo}/{$status}";
                $stats['nuovi_pagamenti']++;
                $preview[] = $rowPreview;
            }

            if (!$confirm) { @unlink($tempPath); return $this->asJson(['success' => true, 'mode' => 'preview', 'preview' => $preview, 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }
            if (!empty($this->errors)) { @unlink($tempPath); return $this->asJson(['success' => false, 'error' => 'Correggere gli errori e riprovare.', 'errors' => $this->errors, 'warnings' => $this->warnings, 'stats' => $stats]); }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $importati = 0;
                foreach ($righe as $idx => $riga) {
                    $rowNum = $idx + 3;
                    $codiceModulo = trim($riga['CODICE_MODULO'] ?? ''); $cfDocente = strtoupper(trim($riga['CF_DOCENTE'] ?? ''));
                    $importoLordo = trim($riga['IMPORTO_LORDO'] ?? ''); $importoNetto = trim($riga['IMPORTO_NETTO'] ?? '');
                    if (empty($codiceModulo) || empty($cfDocente) || !is_numeric($importoLordo) || !is_numeric($importoNetto)) continue;
                    $modulo = Modulo::findOne(['codice' => $codiceModulo]); if (!$modulo) continue;
                    $assegnazione = Assegnazione::findOne(['modulo_id' => $modulo->id, 'docente_codice_fiscale' => $cfDocente]); if (!$assegnazione) continue;
                    $contratto = Contratto::findOne(['assegnazione_id' => $assegnazione->id]); if (!$contratto) continue;
                    $tipo = strtolower(trim($riga['TIPO'] ?? 'saldo')); if (!in_array($tipo, ['acconto', 'saldo', 'rata', 'rimborso'])) $tipo = 'saldo';
                    $status = strtolower(trim($riga['STATUS'] ?? 'pagato')); if (!in_array($status, ['programmato', 'in_attesa', 'pagato', 'annullato'])) $status = 'pagato';
                    $ritenute = trim($riga['RITENUTE'] ?? '');
                    $p = new Pagamento(['contratto_id' => $contratto->id, 'importo_lordo' => (float)$importoLordo, 'importo_netto' => (float)$importoNetto, 'ritenute' => is_numeric($ritenute) ? (float)$ritenute : null, 'tipo' => $tipo, 'status' => $status, 'data_pagamento' => trim($riga['DATA_PAGAMENTO'] ?? '') ?: null, 'riferimento_fattura' => trim($riga['RIFERIMENTO_FATTURA'] ?? '') ?: null, 'numero_mandato' => trim($riga['NUMERO_MANDATO'] ?? '') ?: null, 'note' => trim($riga['NOTE'] ?? '') ?: null]);
                    if ($p->save()) { $importati++; } else { $this->warnings[] = "Riga {$rowNum}: " . json_encode($p->errors); }
                }
                $transaction->commit(); @unlink($tempPath);
                $this->log[] = "Import completato: {$importati} pagamenti creati";
                return $this->asJson(['success' => true, 'mode' => 'import', 'log' => $this->log, 'warnings' => $this->warnings, 'stats' => ['nuovi_pagamenti' => $importati]]);
            } catch (\Exception $e) { $transaction->rollBack(); @unlink($tempPath); throw $e; }
        } catch (\Throwable $e) { @unlink($tempPath); Yii::error('Import pagamenti: ' . $e->getMessage(), __METHOD__); return $this->asJson(['success' => false, 'error' => $e->getMessage()]); }
    }
}
