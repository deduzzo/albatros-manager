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
        ];
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
        $uploadedFile = UploadedFile::getInstanceByName('file');

        if (!$uploadedFile) {
            return $this->asJson(['success' => false, 'error' => 'Nessun file caricato']);
        }

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

            // Esegui import in transazione
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $result = $this->eseguiImport($infoProgetto, $moduli, $docentiEsiti);

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

        } catch (\Exception $e) {
            @unlink($tempPath);
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
                $cells[] = $cell->getValue();
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
                $cells[] = $cell->getValue();
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
                $cells[] = $cell->getValue();
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
    private function eseguiImport($infoProgetto, $moduli, $docentiEsiti)
    {
        // 1. Crea/trova fonte finanziamento
        $fonteCodice = $infoProgetto['FONTE_FINANZIAMENTO'] ?? 'PNRR';
        $fonte = FonteFinanziamento::findOne(['codice' => $fonteCodice]);
        if (!$fonte) {
            $this->errors[] = "Fonte finanziamento '$fonteCodice' non trovata";
            return false;
        }
        $this->log[] = "✓ Fonte finanziamento: {$fonte->nome}";

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

        $this->log[] = "✓ Creati {count($moduli)} moduli";

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
            $provenienza = $riga['PROVENIENZA'] ?? 'TP';
            if ($provenienza === 'P.P.') $provenienza = 'TP';
            if ($provenienza === 'U.R.') $provenienza = 'RU';

            // ASSEGNATARIO
            if (!empty($riga['ASSEGNATARIO'])) {
                $nomeAssegnatario = trim(str_replace('(Rinuncia)', '', $riga['ASSEGNATARIO']));
                $hasRinuncia = strpos($riga['ASSEGNATARIO'], '(Rinuncia)') !== false;

                $docenteCF = $this->creaOTrovaDocente($nomeAssegnatario, $provenienza, $docentiMap);

                // Crea istanza assegnato
                $istanza = $this->creaIstanza($avviso->id, $moduloId, $docenteCF, 'assegnato', $riga);

                if ($istanza && !$hasRinuncia) {
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
                'email' => strtolower($nome . '.' . $cognome) . '@albatros-temp.it',
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
            $this->warnings[] = "Errore creazione istanza per docente $docenteCF";
            return null;
        }

        return $istanza;
    }

    /**
     * Crea assegnazione e contratto
     */
    private function creaAssegnazione($moduloId, $docenteCF, $istanzaId, $riga)
    {
        $assegnazione = new Assegnazione([
            'modulo_id' => $moduloId,
            'docente_codice_fiscale' => $docenteCF,
            'istanza_id' => $istanzaId,
            'data_assegnazione' => $riga['DATA_ESITI_CTS'] ?? date('Y-m-d'),
        ]);

        if (!$assegnazione->save()) {
            $this->warnings[] = "Errore creazione assegnazione";
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
