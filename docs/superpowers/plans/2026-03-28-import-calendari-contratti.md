# Import, Calendari & Contratti Co.Co.Co. - Piano di Implementazione

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementare import assegnazioni standalone, import calendari, report docenti da contrattualizzare, e generazione contratti differenziata (Prestazione Prof. PDF vs Co.Co.Co. PDF)

**Architecture:** Estendere ImportController con nuove azioni per import modulari (assegnazioni e calendari) con template Excel scaricabili. Aggiungere template contratto Co.Co.Co. e differenziare la generazione in ContrattoController in base al tipo contratto. Aggiungere report docenti da contrattualizzare dal calendario in ReportController.

**Tech Stack:** PHP 8.4, Yii2, PhpSpreadsheet (import/export Excel), DOMPDF (PDF), Tabler UI

---

## File Structure

### File da creare:
- `views/contratto/templates/cococo-fse.php` - Template PDF contratto Co.Co.Co. FSE
- `views/contratto/templates/cococo-pnrr.php` - Template PDF contratto Co.Co.Co. PNRR
- `views/report/docenti-da-contrattualizzare.php` - Vista report docenti senza contratto

### File da modificare:
- `controllers/ImportController.php` - Aggiungere azioni import assegnazioni + calendari + template download
- `views/import/index.php` - Rinnovare UI con scelta tipo import
- `controllers/ContrattoController.php:140-220` - Differenziare generazione per tipo contratto
- `controllers/ReportController.php` - Aggiungere report docenti da contrattualizzare
- `views/layouts/menu.php:242-253` - Aggiungere voce menu report

---

## Task 1: Rinnovare pagina Import con scelta tipo

**Files:**
- Modify: `views/import/index.php`
- Modify: `controllers/ImportController.php`

- [ ] **Step 1: Aggiungere azioni download template in ImportController**

Aggiungere 2 nuove azioni al controller per generare programmaticamente i template Excel per assegnazioni e calendari:

```php
// In ImportController.php, aggiungere dopo actionDownloadTemplate():

/**
 * Download template import assegnazioni
 */
public function actionDownloadTemplateAssegnazioni()
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    // Foglio ISTRUZIONI
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('ISTRUZIONI');
    $sheet->setCellValue('A1', 'TEMPLATE IMPORT ASSEGNAZIONI');
    $sheet->setCellValue('A3', 'Questo template serve per importare assegnazioni su moduli/corsi gia\' esistenti nel sistema.');
    $sheet->setCellValue('A5', 'ISTRUZIONI:');
    $sheet->setCellValue('A6', '1. Nel foglio ASSEGNAZIONI compilare una riga per ogni assegnazione');
    $sheet->setCellValue('A7', '2. CODICE_MODULO: deve corrispondere al codice di un modulo gia\' presente nel sistema');
    $sheet->setCellValue('A8', '3. CODICE_FISCALE: codice fiscale del docente (se non esiste, verra\' creato)');
    $sheet->setCellValue('A9', '4. Se CODICE_FISCALE e\' vuoto, il sistema genera un CF fittizio da COGNOME+NOME');
    $sheet->setCellValue('A10', '5. TIPO_CONTRATTO: cococo, partita_iva, occasionale');
    $sheet->setCellValue('A11', '6. PROVENIENZA: TP (Professionista) o RU (Ricercatore Universitario)');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getColumnDimension('A')->setWidth(80);

    // Foglio ASSEGNAZIONI
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('ASSEGNAZIONI');
    $headers = ['CODICE_MODULO*', 'COGNOME*', 'NOME*', 'CODICE_FISCALE', 'PROVENIENZA', 'COMPENSO_ORARIO', 'ORE_ASSEGNATE', 'TIPO_CONTRATTO', 'DATA_ASSEGNAZIONE', 'NOTE'];
    foreach ($headers as $idx => $header) {
        $col = chr(65 + $idx);
        $sheet2->setCellValue($col . '1', $header);
        $sheet2->getColumnDimension($col)->setAutoSize(true);
    }
    // Stile header
    $headerRange = 'A1:J1';
    $sheet2->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']],
    ]);
    // Riga esempio
    $sheet2->setCellValue('A2', 'MOD-001');
    $sheet2->setCellValue('B2', 'Rossi');
    $sheet2->setCellValue('C2', 'Mario');
    $sheet2->setCellValue('D2', 'RSSMRA80A01H501Z');
    $sheet2->setCellValue('E2', 'TP');
    $sheet2->setCellValue('F2', '60');
    $sheet2->setCellValue('G2', '30');
    $sheet2->setCellValue('H2', 'partita_iva');
    $sheet2->setCellValue('I2', '2026-01-15');
    $sheet2->setCellValue('J2', 'Esempio');
    $sheet2->getStyle('A2:J2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF999999'));

    $response = Yii::$app->response;
    $response->format = \yii\web\Response::FORMAT_RAW;
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="TEMPLATE_IMPORT_ASSEGNAZIONI.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    ob_start();
    $writer->save('php://output');
    $response->content = ob_get_clean();
    return $response;
}

/**
 * Download template import calendari
 */
public function actionDownloadTemplateCalendari()
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    // Foglio ISTRUZIONI
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('ISTRUZIONI');
    $sheet->setCellValue('A1', 'TEMPLATE IMPORT CALENDARI');
    $sheet->setCellValue('A3', 'Questo template serve per importare lezioni/calendari su moduli gia\' esistenti.');
    $sheet->setCellValue('A5', 'ISTRUZIONI:');
    $sheet->setCellValue('A6', '1. Nel foglio CALENDARIO compilare una riga per ogni lezione');
    $sheet->setCellValue('A7', '2. CODICE_MODULO: deve corrispondere al codice di un modulo presente nel sistema');
    $sheet->setCellValue('A8', '3. CF_DOCENTE: codice fiscale del docente assegnato al modulo');
    $sheet->setCellValue('A9', '4. DATA: formato AAAA-MM-GG (es. 2026-04-15)');
    $sheet->setCellValue('A10', '5. ORA_INIZIO e ORA_FINE: formato HH:MM (es. 09:00, 13:00)');
    $sheet->setCellValue('A11', '6. Le ore vengono calcolate automaticamente dalla differenza');
    $sheet->setCellValue('A12', '7. STATUS: programmata (default), completata, annullata, recupero');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getColumnDimension('A')->setWidth(80);

    // Foglio CALENDARIO
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('CALENDARIO');
    $headers = ['CODICE_MODULO*', 'CF_DOCENTE*', 'DATA*', 'ORA_INIZIO*', 'ORA_FINE*', 'AULA', 'ARGOMENTO', 'DESCRIZIONE_ATTIVITA', 'STATUS', 'NOTE'];
    foreach ($headers as $idx => $header) {
        $col = chr(65 + $idx);
        $sheet2->setCellValue($col . '1', $header);
        $sheet2->getColumnDimension($col)->setAutoSize(true);
    }
    $headerRange = 'A1:J1';
    $sheet2->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']],
    ]);
    // Riga esempio
    $sheet2->setCellValue('A2', 'MOD-001');
    $sheet2->setCellValue('B2', 'RSSMRA80A01H501Z');
    $sheet2->setCellValue('C2', '2026-04-15');
    $sheet2->setCellValue('D2', '09:00');
    $sheet2->setCellValue('E2', '13:00');
    $sheet2->setCellValue('F2', 'Aula 1');
    $sheet2->setCellValue('G2', 'Introduzione al modulo');
    $sheet2->setCellValue('H2', 'Lezione introduttiva');
    $sheet2->setCellValue('I2', 'programmata');
    $sheet2->getStyle('A2:J2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF999999'));

    $response = Yii::$app->response;
    $response->format = \yii\web\Response::FORMAT_RAW;
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="TEMPLATE_IMPORT_CALENDARI.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    ob_start();
    $writer->save('php://output');
    $response->content = ob_get_clean();
    return $response;
}
```

- [ ] **Step 2: Rinnovare la vista import/index.php**

Sostituire la vista con 3 card per i 3 tipi di import, ciascuna con il proprio form e template scaricabile:

La nuova vista deve avere:
1. **Card "Import Completo"** - Import progetto+moduli+docenti (esistente)
2. **Card "Import Assegnazioni"** - Import standalone su moduli esistenti (NUOVO)
3. **Card "Import Calendari"** - Import lezioni (NUOVO)

Ogni card ha:
- Pulsante "Scarica Template"
- Form upload file
- Area risultati

Usare tab Tabler (`<ul class="nav nav-tabs">`) per organizzare i 3 tipi. Il JS di upload va duplicato per gestire 3 endpoint diversi.

- [ ] **Step 3: Commit**

```bash
git add views/import/index.php controllers/ImportController.php
git commit -m "feat: rinnovare pagina import con 3 tipi e template scaricabili"
```

---

## Task 2: Import Assegnazioni Standalone

**Files:**
- Modify: `controllers/ImportController.php`

- [ ] **Step 1: Aggiungere action uploadAssegnazioni**

```php
/**
 * Upload e import assegnazioni da Excel
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

    try {
        $spreadsheet = IOFactory::load($tempPath);
        $sheet = $spreadsheet->getSheetByName('ASSEGNAZIONI');
        if (!$sheet) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'error' => 'Foglio "ASSEGNAZIONI" non trovato']);
        }

        // Leggi righe
        $righe = $this->leggiRigheFoglio($sheet);

        if (empty($righe)) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'error' => 'Nessuna riga dati trovata']);
        }

        // Valida
        foreach ($righe as $idx => $riga) {
            if (empty($riga['CODICE_MODULO'])) {
                $this->errors[] = "Riga " . ($idx + 2) . ": CODICE_MODULO obbligatorio";
            }
            if (empty($riga['COGNOME'])) {
                $this->errors[] = "Riga " . ($idx + 2) . ": COGNOME obbligatorio";
            }
            if (empty($riga['NOME'])) {
                $this->errors[] = "Riga " . ($idx + 2) . ": NOME obbligatorio";
            }
            // Verifica che il modulo esista
            if (!empty($riga['CODICE_MODULO'])) {
                $modulo = Modulo::findOne(['codice' => $riga['CODICE_MODULO']]);
                if (!$modulo) {
                    $this->errors[] = "Riga " . ($idx + 2) . ": Modulo '{$riga['CODICE_MODULO']}' non trovato nel sistema";
                }
            }
        }

        if (!empty($this->errors)) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'errors' => $this->errors]);
        }

        // Esegui import in transazione
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $countAssegnazioni = 0;
            $countDocenti = 0;
            $docentiMap = [];

            foreach ($righe as $riga) {
                $codiceModulo = $riga['CODICE_MODULO'];
                $modulo = Modulo::findOne(['codice' => $codiceModulo]);

                // Trova o crea docente
                $cf = $riga['CODICE_FISCALE'] ?? '';
                if (empty($cf)) {
                    $nomeCompleto = trim($riga['COGNOME'] . ' ' . $riga['NOME']);
                    $cf = $this->creaOTrovaDocente($riga['NOME'] . ' ' . $riga['COGNOME'], $riga['PROVENIENZA'] ?? 'TP', $docentiMap);
                } else {
                    $cf = strtoupper(trim($cf));
                    $docente = Docente::findOne(['codice_fiscale' => $cf]);
                    if (!$docente) {
                        $docente = new Docente([
                            'codice_fiscale' => $cf,
                            'cognome' => $riga['COGNOME'],
                            'nome' => $riga['NOME'],
                            'provenienza' => $riga['PROVENIENZA'] ?? 'TP',
                        ]);
                        if ($docente->save()) {
                            $countDocenti++;
                            $this->log[] = "  + Docente creato: {$riga['COGNOME']} {$riga['NOME']}";
                        }
                    }
                }

                if (!$cf) continue;

                // Verifica assegnazione esistente
                $assegnazione = Assegnazione::findOne([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                ]);

                if ($assegnazione) {
                    $this->warnings[] = "Assegnazione gia\' esistente per {$riga['COGNOME']} {$riga['NOME']} sul modulo {$codiceModulo} - saltata";
                    continue;
                }

                // Cerca o crea istanza
                $avviso = $modulo->avviso;
                $istanza = Istanza::findOne([
                    'avviso_id' => $avviso->id,
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                ]);
                if (!$istanza) {
                    $istanza = new Istanza([
                        'avviso_id' => $avviso->id,
                        'modulo_id' => $modulo->id,
                        'docente_codice_fiscale' => $cf,
                        'data_presentazione' => $riga['DATA_ASSEGNAZIONE'] ?? date('Y-m-d'),
                        'esito_cts' => 'assegnato',
                        'status' => 'valutata',
                    ]);
                    $istanza->save(false);
                }

                // Crea assegnazione
                $assegnazione = new Assegnazione([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                    'istanza_id' => $istanza->id,
                    'data_assegnazione' => $riga['DATA_ASSEGNAZIONE'] ?? date('Y-m-d'),
                ]);

                if (!$assegnazione->save()) {
                    $this->warnings[] = "Errore assegnazione {$riga['COGNOME']}: " . json_encode($assegnazione->errors);
                    continue;
                }

                // Crea contratto
                $tipoContratto = 'cococo';
                $tipoInput = strtoupper($riga['TIPO_CONTRATTO'] ?? '');
                if (strpos($tipoInput, 'P') !== false || strpos($tipoInput, 'PARTITA') !== false || strpos($tipoInput, 'IVA') !== false) {
                    $tipoContratto = 'partita_iva';
                } elseif (strpos($tipoInput, 'OCC') !== false) {
                    $tipoContratto = 'occasionale';
                }

                $compensoOrario = $riga['COMPENSO_ORARIO'] ?? $modulo->compenso_orario ?? 60;
                $oreAssegnate = $riga['ORE_ASSEGNATE'] ?? $modulo->ore_totali ?? 20;

                $contratto = new Contratto([
                    'assegnazione_id' => $assegnazione->id,
                    'tipo' => $tipoContratto,
                    'numero_contratto' => 'CONTR-' . $assegnazione->id,
                    'data_stipula' => $riga['DATA_ASSEGNAZIONE'] ?? date('Y-m-d'),
                    'data_inizio' => date('Y-m-d'),
                    'data_fine' => date('Y-m-d', strtotime('+6 months')),
                    'compenso_orario' => $compensoOrario,
                    'ore_contrattualizzate' => $oreAssegnate,
                    'compenso_totale' => $compensoOrario * $oreAssegnate,
                    'status' => 'bozza',
                ]);

                if (!$contratto->save()) {
                    $this->warnings[] = "Errore contratto: " . json_encode($contratto->errors);
                }

                $countAssegnazioni++;
                $this->log[] = "  + Assegnazione: {$riga['COGNOME']} {$riga['NOME']} -> {$codiceModulo}";
            }

            $transaction->commit();
            @unlink($tempPath);

            $this->log[] = "Import completato: {$countAssegnazioni} assegnazioni create";

            return $this->asJson([
                'success' => true,
                'log' => $this->log,
                'warnings' => $this->warnings,
            ]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    } catch (\Throwable $e) {
        @unlink($tempPath);
        return $this->asJson(['success' => false, 'error' => 'Errore: ' . $e->getMessage()]);
    }
}
```

- [ ] **Step 2: Aggiungere metodo helper leggiRigheFoglio**

```php
/**
 * Leggi righe da un foglio Excel generico (header in riga 1, dati da riga 2)
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
            } elseif ($value === null) {
                $value = '';
            } else {
                $value = (string)$value;
            }
            $cells[] = trim($value);
        }

        if ($rowIndex === 1) {
            $headers = array_map(function($h) {
                return str_replace('*', '', trim($h));
            }, $cells);
        } else {
            // Skip empty rows
            $nonEmpty = array_filter($cells, fn($c) => $c !== '');
            if (empty($nonEmpty)) continue;

            // Skip example row (gray text)
            if (isset($cells[0]) && $cells[0] === 'MOD-001') continue;

            $riga = array_combine($headers, $cells);
            $righe[] = $riga;
        }
    }

    return $righe;
}
```

- [ ] **Step 3: Aggiungere verbs per le nuove azioni**

In `behaviors()` di ImportController, aggiungere:

```php
'verbs' => [
    'class' => \yii\filters\VerbFilter::class,
    'actions' => [
        'upload' => ['POST'],
        'upload-assegnazioni' => ['POST'],
        'upload-calendari' => ['POST'],
    ],
],
```

E in `beforeAction()`:

```php
if (in_array($action->id, ['upload', 'upload-assegnazioni', 'upload-calendari'])) {
    $this->enableCsrfValidation = false;
}
```

- [ ] **Step 4: Commit**

```bash
git add controllers/ImportController.php
git commit -m "feat: aggiungere import assegnazioni standalone"
```

---

## Task 3: Import Calendari

**Files:**
- Modify: `controllers/ImportController.php`

- [ ] **Step 1: Aggiungere action uploadCalendari**

```php
/**
 * Upload e import calendari/lezioni da Excel
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

    try {
        $spreadsheet = IOFactory::load($tempPath);
        $sheet = $spreadsheet->getSheetByName('CALENDARIO');
        if (!$sheet) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'error' => 'Foglio "CALENDARIO" non trovato']);
        }

        $righe = $this->leggiRigheFoglio($sheet);

        if (empty($righe)) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'error' => 'Nessuna riga dati trovata']);
        }

        // Uso di app\models\Calendario gia' importato in cima
        // Validazione
        foreach ($righe as $idx => $riga) {
            $rowNum = $idx + 2;
            if (empty($riga['CODICE_MODULO'])) {
                $this->errors[] = "Riga {$rowNum}: CODICE_MODULO obbligatorio";
            } else {
                $modulo = Modulo::findOne(['codice' => $riga['CODICE_MODULO']]);
                if (!$modulo) {
                    $this->errors[] = "Riga {$rowNum}: Modulo '{$riga['CODICE_MODULO']}' non trovato";
                }
            }
            if (empty($riga['CF_DOCENTE'])) {
                $this->errors[] = "Riga {$rowNum}: CF_DOCENTE obbligatorio";
            } else {
                $docente = Docente::findOne(['codice_fiscale' => strtoupper(trim($riga['CF_DOCENTE']))]);
                if (!$docente) {
                    $this->errors[] = "Riga {$rowNum}: Docente con CF '{$riga['CF_DOCENTE']}' non trovato";
                }
            }
            if (empty($riga['DATA'])) {
                $this->errors[] = "Riga {$rowNum}: DATA obbligatoria";
            }
            if (empty($riga['ORA_INIZIO'])) {
                $this->errors[] = "Riga {$rowNum}: ORA_INIZIO obbligatoria";
            }
            if (empty($riga['ORA_FINE'])) {
                $this->errors[] = "Riga {$rowNum}: ORA_FINE obbligatoria";
            }
        }

        if (!empty($this->errors)) {
            @unlink($tempPath);
            return $this->asJson(['success' => false, 'errors' => $this->errors]);
        }

        // Import in transazione
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $countLezioni = 0;

            foreach ($righe as $riga) {
                $modulo = Modulo::findOne(['codice' => $riga['CODICE_MODULO']]);
                $cf = strtoupper(trim($riga['CF_DOCENTE']));

                // Normalizza data (potrebbe essere formato Excel numerico)
                $data = $riga['DATA'];
                if (is_numeric($data)) {
                    // Converti da serial Excel a data
                    $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$data)->format('Y-m-d');
                } elseif (strpos($data, '/') !== false) {
                    // Formato dd/mm/yyyy
                    $parts = explode('/', $data);
                    if (count($parts) === 3) {
                        $data = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                }

                // Normalizza orari
                $oraInizio = $this->normalizzaOrario($riga['ORA_INIZIO']);
                $oraFine = $this->normalizzaOrario($riga['ORA_FINE']);

                // Calcola ore
                $ore = (strtotime($oraFine) - strtotime($oraInizio)) / 3600;

                $status = !empty($riga['STATUS']) ? strtolower(trim($riga['STATUS'])) : 'programmata';
                if (!in_array($status, ['programmata', 'completata', 'annullata', 'recupero'])) {
                    $status = 'programmata';
                }

                // Verifica duplicato (stessa data, modulo, docente, stesse ore)
                $existing = \app\models\Calendario::findOne([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                    'data' => $data,
                    'ora_inizio' => $oraInizio,
                ]);

                if ($existing) {
                    $this->warnings[] = "Lezione gia\' esistente: {$riga['CODICE_MODULO']} {$data} {$oraInizio} - saltata";
                    continue;
                }

                $calendario = new \app\models\Calendario([
                    'modulo_id' => $modulo->id,
                    'docente_codice_fiscale' => $cf,
                    'data' => $data,
                    'ora_inizio' => $oraInizio,
                    'ora_fine' => $oraFine,
                    'ore' => round($ore, 2),
                    'aula' => $riga['AULA'] ?? null,
                    'argomento' => $riga['ARGOMENTO'] ?? null,
                    'descrizione_attivita' => $riga['DESCRIZIONE_ATTIVITA'] ?? null,
                    'status' => $status,
                    'note' => $riga['NOTE'] ?? null,
                ]);

                if (!$calendario->save()) {
                    $this->warnings[] = "Errore lezione {$data}: " . json_encode($calendario->errors);
                    continue;
                }

                $countLezioni++;
                $this->log[] = "  + Lezione: {$riga['CODICE_MODULO']} - {$data} {$oraInizio}-{$oraFine}";
            }

            $transaction->commit();
            @unlink($tempPath);

            $this->log[] = "Import completato: {$countLezioni} lezioni create";

            return $this->asJson([
                'success' => true,
                'log' => $this->log,
                'warnings' => $this->warnings,
            ]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    } catch (\Throwable $e) {
        @unlink($tempPath);
        return $this->asJson(['success' => false, 'error' => 'Errore: ' . $e->getMessage()]);
    }
}

/**
 * Normalizza formato orario in HH:MM
 */
private function normalizzaOrario($orario)
{
    $orario = trim($orario);

    // Se e' un numero decimale (formato Excel time fraction)
    if (is_numeric($orario) && (float)$orario < 1) {
        $totalMinutes = round((float)$orario * 24 * 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    // Se e' gia' HH:MM o HH:MM:SS
    if (preg_match('/^(\d{1,2}):(\d{2})/', $orario, $matches)) {
        return sprintf('%02d:%02d', (int)$matches[1], (int)$matches[2]);
    }

    return $orario;
}
```

- [ ] **Step 2: Aggiungere use Calendario in cima al controller**

Aggiungere `use app\models\Calendario;` nella sezione use di ImportController.

- [ ] **Step 3: Commit**

```bash
git add controllers/ImportController.php
git commit -m "feat: aggiungere import calendari da Excel"
```

---

## Task 4: Report Docenti da Contrattualizzare dal Calendario

**Files:**
- Modify: `controllers/ReportController.php`
- Create: `views/report/docenti-da-contrattualizzare.php`
- Modify: `views/layouts/menu.php`

- [ ] **Step 1: Aggiungere azione in ReportController**

```php
/**
 * Report: docenti con lezioni in calendario ma senza contratto
 */
public function actionDocentiDaContrattualizzare($progetto_id = null)
{
    // Query: docenti che hanno lezioni in calendario ma non hanno un contratto
    $query = (new Query())
        ->select([
            'd.codice_fiscale',
            'd.cognome',
            'd.nome',
            'd.provenienza',
            'd.email',
            'd.telefono',
            'm.codice AS codice_modulo',
            'm.titolo AS titolo_modulo',
            'm.ore_totali',
            'm.compenso_orario',
            'p.titolo AS titolo_progetto',
            'p.id AS progetto_id',
            'COUNT(cal.id) AS num_lezioni',
            'COALESCE(SUM(cal.ore), 0) AS ore_calendario',
        ])
        ->from('calendario cal')
        ->innerJoin('docente d', 'd.codice_fiscale = cal.docente_codice_fiscale AND d.deleted_at IS NULL')
        ->innerJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
        ->innerJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
        ->innerJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
        ->leftJoin('assegnazione ass', 'ass.modulo_id = cal.modulo_id AND ass.docente_codice_fiscale = cal.docente_codice_fiscale AND ass.deleted_at IS NULL')
        ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
        ->where(['cal.deleted_at' => null])
        ->andWhere(['cal.status' => ['programmata', 'completata']])
        ->andWhere(['c.id' => null]) // SENZA contratto
        ->groupBy(['d.codice_fiscale', 'm.id', 'p.id']);

    if ($progetto_id) {
        $query->andWhere(['p.id' => $progetto_id]);
    }

    $query->orderBy(['p.titolo' => SORT_ASC, 'd.cognome' => SORT_ASC, 'm.codice' => SORT_ASC]);

    $docenti = $query->all();

    // Lista progetti per filtro
    $progetti = Progetto::find()->orderBy('titolo')->all();

    return $this->render('docenti-da-contrattualizzare', [
        'docenti' => $docenti,
        'progetti' => $progetti,
        'progetto_id' => $progetto_id,
    ]);
}
```

- [ ] **Step 2: Creare la vista report**

Creare `views/report/docenti-da-contrattualizzare.php` con:
- Filtro per progetto (dropdown)
- Tabella con: Docente, CF, Modulo, Progetto, Lezioni, Ore Calendario, Compenso Stimato
- Pulsante "Crea Contratto" per ogni riga (link a `/contratto/create?assegnazione_id=...`)
- Riepilogo totali in fondo
- Stile Tabler con badge e icone

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\Alert;

$this->title = 'Docenti da Contrattualizzare';
$this->params['breadcrumbs'][] = ['label' => 'Report', 'url' => ['/report/statistiche']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-docenti-contrattualizzare">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-alert-triangle me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Docenti con lezioni in calendario ma senza contratto stipulato
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= Alert::widget() ?>

            <!-- Filtro progetto -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Filtra per Progetto</label>
                            <select name="progetto_id" class="form-select">
                                <option value="">-- Tutti i progetti --</option>
                                <?php foreach ($progetti as $p): ?>
                                    <option value="<?= $p->id ?>" <?= $progetto_id == $p->id ? 'selected' : '' ?>>
                                        <?= Html::encode($p->codice_progetto . ' - ' . $p->titolo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i> Filtra
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($docenti)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3"><i class="ti ti-check text-green" style="font-size: 3rem;"></i></div>
                        <h3>Tutti i docenti con lezioni hanno un contratto</h3>
                        <p class="text-muted">Non ci sono docenti da contrattualizzare.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Stat cards -->
                <div class="row row-deck row-cards mb-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Docenti</div>
                                </div>
                                <div class="h1 mb-0"><?= count(array_unique(array_column($docenti, 'codice_fiscale'))) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Moduli senza contratto</div>
                                </div>
                                <div class="h1 mb-0"><?= count($docenti) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Ore totali</div>
                                </div>
                                <div class="h1 mb-0"><?= number_format(array_sum(array_column($docenti, 'ore_calendario')), 1) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">Compenso stimato</div>
                                </div>
                                <?php
                                $totaleStimato = 0;
                                foreach ($docenti as $d) {
                                    $totaleStimato += $d['ore_calendario'] * ($d['compenso_orario'] ?: 60);
                                }
                                ?>
                                <div class="h1 mb-0">&euro; <?= number_format($totaleStimato, 2, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabella -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Docente</th>
                                    <th>Modulo</th>
                                    <th>Progetto</th>
                                    <th>Lezioni</th>
                                    <th>Ore</th>
                                    <th>Compenso stimato</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($docenti as $d): ?>
                                    <tr>
                                        <td>
                                            <strong><?= Html::encode($d['cognome'] . ' ' . $d['nome']) ?></strong>
                                            <div class="text-muted small"><?= Html::encode($d['codice_fiscale']) ?></div>
                                            <span class="badge <?= $d['provenienza'] === 'RU' ? 'bg-indigo-lt' : 'bg-blue-lt' ?>">
                                                <?= $d['provenienza'] === 'RU' ? 'Ricercatore' : 'Professionista' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= Html::encode($d['codice_modulo']) ?></strong>
                                            <div class="text-muted small"><?= Html::encode($d['titolo_modulo']) ?></div>
                                        </td>
                                        <td><?= Html::encode($d['titolo_progetto']) ?></td>
                                        <td><span class="badge bg-azure-lt"><?= $d['num_lezioni'] ?></span></td>
                                        <td><?= number_format($d['ore_calendario'], 1) ?>h</td>
                                        <td>&euro; <?= number_format($d['ore_calendario'] * ($d['compenso_orario'] ?: 60), 2, ',', '.') ?></td>
                                        <td>
                                            <?= Html::a(
                                                '<i class="ti ti-file-plus me-1"></i>Contrattualizza',
                                                ['/assegnazione/index', 'AssegnazioneSearch[docente_codice_fiscale]' => $d['codice_fiscale']],
                                                ['class' => 'btn btn-sm btn-green']
                                            ) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Aggiungere voce nel menu Report**

In `views/layouts/menu.php`, nella sezione Report (dopo "Export"), aggiungere:

```php
<a class="dropdown-item <?= $currentController == 'report' && $currentAction == 'docenti-da-contrattualizzare' ? 'active' : '' ?>" href="<?= Url::to(['/report/docenti-da-contrattualizzare']) ?>">
    <i class="ti ti-alert-triangle me-2"></i> Docenti da Contrattualizzare
</a>
```

- [ ] **Step 4: Commit**

```bash
git add controllers/ReportController.php views/report/docenti-da-contrattualizzare.php views/layouts/menu.php
git commit -m "feat: aggiungere report docenti da contrattualizzare dal calendario"
```

---

## Task 5: Template Contratto Co.Co.Co. PNRR

**Files:**
- Create: `views/contratto/templates/cococo-pnrr.php`

- [ ] **Step 1: Creare template Co.Co.Co. PNRR**

Creare `views/contratto/templates/cococo-pnrr.php` basato sul formato del PDF fornito (rendicontazione.pdf pagg. 6-13). Struttura:

1. Header PNRR (Futura/Albatros/MIM/Italiadomani)
2. Numero protocollo: `N.___ PNRR-M4C1I1.5/F/____`
3. Titolo: **COLLABORAZIONE COORDINATA E CONTINUATIVA** *(ART. 409 c.p.c. e art. 2 D.Lgs. n. 81/2015)*
4. Descrizione incarico (dinamica dal modulo)
5. **TRA** - Fondazione ITS Academy Albatros (dati da configurazione)
6. **E** - Docente/Collaboratore (dati dal model docente)
7. **PREMESSO** - 5+ punti che descrivono il contesto
8. **TUTTO CIO' PREMESSO**
9. **Art. 1 - Premesse**: Le premesse costituiscono parte integrante
10. **Art. 2 - Natura dell'incarico e Finalita'**: Collaborazione art. 2222 cc, autonomia
11. **Art. 3 - Attivita'**: Dettaglio attivita' del docente (lista puntata)
12. **Art. 4 - Sede**: Sede di erogazione
13. **Art. 5 - Durata**: Date inizio/fine
14. **Art. 6 - Compenso**: Compenso orario, totale, modalita' pagamento, esenzione IVA art.5
15. **Art. 7 - Trattamento dati personali**: GDPR
16. **Art. 8 - Recesso**: Facolta' recesso entrambe le parti
17. **Art. 9 - Controversie**: Foro di Messina
18. **Art. 10 - Rinvio**: Legge italiana, Reg. UE 2021/241
19. Allegati (CV, Dichiarazione, Autorizzazione)
20. Firme bilaterali

Il template deve essere in HTML per DOMPDF, con variabili PHP per tutti i dati dinamici. Usa lo stesso pattern dei template `fse.php` e `pnrr.php` esistenti (riceve `$contratto`, `$assegnazione`, `$docente`, `$modulo`, `$avviso`, `$progetto`, `$configurazione`).

Font: DejaVu Sans. Footer con dati fondazione + ISO 9001.

- [ ] **Step 2: Commit**

```bash
git add views/contratto/templates/cococo-pnrr.php
git commit -m "feat: aggiungere template contratto Co.Co.Co. PNRR"
```

---

## Task 6: Template Contratto Co.Co.Co. FSE

**Files:**
- Create: `views/contratto/templates/cococo-fse.php`

- [ ] **Step 1: Creare template Co.Co.Co. FSE**

Identico al template PNRR ma con:
- Header FSE (UE/Ministero/FSE/Regione Siciliana) - stesse loghi del template fse.php esistente
- Riferimenti normativi FSE al posto di PNRR
- Colori tema blu (#003399) invece di verde PNRR
- Nessun riferimento a NextGenerationEU/Futura
- Art. 6 Compenso: riferimenti a PO FSE Sicilia 2014-2020 o FSE+ 2021-2027

- [ ] **Step 2: Commit**

```bash
git add views/contratto/templates/cococo-fse.php
git commit -m "feat: aggiungere template contratto Co.Co.Co. FSE"
```

---

## Task 7: Differenziare Generazione Contratti in ContrattoController

**Files:**
- Modify: `controllers/ContrattoController.php:140-220`

- [ ] **Step 1: Modificare actionGenera per selezionare template in base a tipo contratto + fonte**

Nella `actionGenera()` di ContrattoController, sostituire la logica di selezione template (righe 172-179):

```php
// Vecchio codice:
// $template = 'fse';
// if (strpos($codiceFonte, 'PNRR') !== false || strpos($codiceFonte, 'MIM') !== false) {
//     $template = 'pnrr';
// }

// Nuovo codice - differenzia per tipo contratto + fonte finanziamento:
$isPNRR = strpos($codiceFonte, 'PNRR') !== false || strpos($codiceFonte, 'MIM') !== false;

if ($model->tipo === Contratto::TIPO_COCOCO) {
    // Co.Co.Co. -> template bilaterale con articoli
    $template = $isPNRR ? 'cococo-pnrr' : 'cococo-fse';
} else {
    // Prestazione professionale / Occasionale -> template lettera di incarico
    $template = $isPNRR ? 'pnrr' : 'fse';
}
```

Nessun'altra modifica necessaria: il resto della logica (renderPartial, PdfGenerator, salvataggio file) rimane identico.

- [ ] **Step 2: Commit**

```bash
git add controllers/ContrattoController.php
git commit -m "feat: differenziare generazione contratto per tipo (PP vs Co.Co.Co.) e fonte"
```

---

## Task 8: Aggiornare la vista Import con UI completa a tab

**Files:**
- Modify: `views/import/index.php`

- [ ] **Step 1: Riscrivere la vista con 3 tab**

Riscrivere `views/import/index.php` con:
- Header pagina con titolo
- 3 Tab Tabler: "Import Completo", "Import Assegnazioni", "Import Calendari"
- Ogni tab contiene:
  - Card istruzioni specifiche per quel tipo
  - Pulsante "Scarica Template" (route diversa per ogni tipo)
  - Form upload con checkbox overwrite dove applicabile
  - Area risultati con progress bar + log + errori + warnings

Il JS deve gestire 3 endpoint diversi:
- Tab 1: `POST /import/upload` (esistente)
- Tab 2: `POST /import/upload-assegnazioni`
- Tab 3: `POST /import/upload-calendari`

Usare una funzione JS generica `submitImport(formId, uploadUrl)` per evitare duplicazione.

- [ ] **Step 2: Commit**

```bash
git add views/import/index.php
git commit -m "feat: UI import con 3 tab (completo, assegnazioni, calendari)"
```

---

## Task 9: Test manuale e verifica finale

- [ ] **Step 1: Verificare che Docker sia attivo e l'app funzioni**

```bash
cd /Users/deduzzo/dev/albatros-manager-yii2
docker-compose ps
```

- [ ] **Step 2: Verificare accesso alle nuove pagine**

Verificare nel browser:
1. `http://localhost:8000/import` - Pagina import con 3 tab
2. `http://localhost:8000/import/download-template-assegnazioni` - Download template assegnazioni
3. `http://localhost:8000/import/download-template-calendari` - Download template calendari
4. `http://localhost:8000/report/docenti-da-contrattualizzare` - Report docenti
5. `http://localhost:8000/contratto/view?id=X` - Verificare pulsante genera (con contratto cococo)

- [ ] **Step 3: Commit finale e aggiornare CHANGELOG.md**

```bash
# Aggiungere al CHANGELOG.md le voci:
# - {admin} Import Assegnazioni standalone su moduli esistenti
# - {admin} Import Calendari/Lezioni da Excel
# - {admin} Report Docenti da Contrattualizzare dal Calendario
# - {admin} Generazione contratti Co.Co.Co. (PDF differenziato da Prestazione Professionale)
# - {admin} Template Excel scaricabili per ogni tipo di import
```

---

## Note importanti

### Documenti di riferimento
- **Contratto PP FSE**: `contabilita'.pdf` pagg. 1-2 (gia' implementato in `templates/fse.php`)
- **Contratto PP PNRR**: `contabilita'.pdf` pagg. 3-4 (gia' implementato in `templates/pnrr.php`)
- **Contratto Co.Co.Co. FSE**: `contabilita'.pdf` pagg. 5-8 (da implementare)
- **Contratto Co.Co.Co. PNRR**: `rendicontazione.pdf` pagg. 6-13 (da implementare, con 10 articoli)

### Tipi contratto nel sistema
- `partita_iva` = Prestazione Professionale (template: fse.php o pnrr.php)
- `occasionale` = Prestazione Occasionale (template: fse.php o pnrr.php)
- `cococo` = Co.Co.Co. (template: cococo-fse.php o cococo-pnrr.php)

### Export Excel Co.Co.Co. per consulente
L'export Excel per consulente del lavoro esiste gia' in `ReportController::actionExportConsulente()`. Non necessita modifiche - esporta tutti i docenti con contratto per progetto, includendo il tipo contratto nella colonna "Tipo Contratto".
