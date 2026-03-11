<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\CriterioValutazioneDocente;
use app\models\Docente;
use app\models\ValutazioneDocente;
use app\models\ValutazioneDocentePunteggio;

/**
 * Importa valutazioni docenti da file CSV.
 */
class ImportValutazioniController extends Controller
{
    public function actionIndex($dir = null)
    {
        $dir = $dir ?: Yii::getAlias('@app/docs/DATI REALI/valutazioni');
        $files = glob($dir . '/*.csv');

        if (empty($files)) {
            $this->stderr("Nessun CSV trovato in $dir\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Trovati " . count($files) . " file CSV\n\n", Console::BOLD);

        // Carica criteri - match per ordine colonna (i CSV hanno sempre lo stesso ordine)
        $criteri = CriterioValutazioneDocente::find()
            ->where(['attivo' => 1])
            ->orderBy('ordinamento')
            ->all();
        $this->stdout("Criteri in DB: " . count($criteri) . "\n");

        // Carica docenti per matching
        $docenti = Docente::find()->all();
        $docenteMap = [];
        foreach ($docenti as $d) {
            $keys = [
                mb_strtolower(trim($d->cognome . ' ' . $d->nome)),
                mb_strtolower(trim($d->nome . ' ' . $d->cognome)),
                mb_strtolower(trim($d->cognome)),
            ];
            foreach ($keys as $k) {
                $docenteMap[$k] = $d->codice_fiscale;
            }
        }
        $this->stdout("Docenti in DB: " . count($docenti) . "\n\n");

        $totImportate = 0;
        $totErrori = 0;
        $nonMatchati = [];

        foreach ($files as $file) {
            $this->stdout("--- " . basename($file) . " ---\n", Console::BOLD);
            $result = $this->importFile($file, $criteri, $docenteMap, $nonMatchati);
            $totImportate += $result['importate'];
            $totErrori += $result['errori'];
            $this->stdout("  Importate: {$result['importate']}, Errori: {$result['errori']}\n");
        }

        $this->stdout("\n=== RISULTATO ===\n", Console::BOLD, Console::FG_GREEN);
        $this->stdout("Valutazioni importate: $totImportate\n");
        $this->stdout("Errori: $totErrori\n");
        $this->stdout("Docenti non matchati: " . count($nonMatchati) . "\n");
        if (!empty($nonMatchati)) {
            $this->stdout("\nNomi non trovati:\n", Console::FG_YELLOW);
            foreach ($nonMatchati as $nome => $_) {
                $this->stdout("  - $nome\n");
            }
        }

        $this->stdout("\nTotale valutazioni in DB: " . ValutazioneDocente::find()->count() . "\n");
        $this->stdout("Totale punteggi dettaglio: " . ValutazioneDocentePunteggio::find()->count() . "\n");

        return ExitCode::OK;
    }

    private function importFile($file, $criteri, &$docenteMap, &$nonMatchati)
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, ',', '"', '\\');

        // Identifica colonne speciali
        $emailIdx = null;
        $nomeIdx = null;
        $docenteIdx = null;
        $noteIdx = null;
        $dateIdx = null;
        $urlIdx = null;
        $punteggioColonne = []; // indice colonna => true (colonne numeriche = punteggi)

        foreach ($header as $i => $col) {
            $col = trim($col);
            $colLower = mb_strtolower($col);

            if (stripos($col, 'Email') !== false && $emailIdx === null) {
                $emailIdx = $i;
            } elseif ((stripos($col, 'Nome e Cognome') !== false || $col === 'Nome e Cognome') && $nomeIdx === null) {
                $nomeIdx = $i;
            } elseif (stripos($col, 'Docente') !== false || stripos($col, 'DOCENTE') !== false) {
                $docenteIdx = $i;
            } elseif ($col === 'Note') {
                $noteIdx = $i;
            } elseif (stripos($col, 'Submission Date') !== false) {
                $dateIdx = $i;
            } elseif (stripos($col, 'URL') !== false) {
                $urlIdx = $i;
            } elseif (stripos($col, 'Timezone') !== false) {
                // skip
            } else {
                // Colonna di punteggio (le intestazioni sono le domande)
                $punteggioColonne[$i] = true;
            }
        }

        // Mappa colonne punteggio ai criteri per ordine
        $criteriIdx = []; // criterio_id => col_index
        $puntColIdxOrdered = array_keys($punteggioColonne);
        foreach ($puntColIdxOrdered as $ordine => $colIdx) {
            if (isset($criteri[$ordine])) {
                $criteriIdx[$criteri[$ordine]->id] = $colIdx;
            }
        }

        $this->stdout("  Colonne punteggio: " . count($punteggioColonne) . ", Criteri mappati: " . count($criteriIdx) . "\n");

        $importate = 0;
        $errori = 0;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) < 5) continue;

            $docenteNome = $docenteIdx !== null ? trim($row[$docenteIdx] ?? '') : '';
            if (empty($docenteNome)) continue;

            $studenteEmail = $emailIdx !== null ? trim($row[$emailIdx] ?? '') : '';
            $studenteNome = $nomeIdx !== null ? trim($row[$nomeIdx] ?? '') : '';
            $note = ($noteIdx !== null && !empty(trim($row[$noteIdx] ?? ''))) ? trim($row[$noteIdx]) : null;
            $data = $dateIdx !== null ? $this->parseDate($row[$dateIdx] ?? '') : date('Y-m-d');
            $url = $urlIdx !== null ? trim($row[$urlIdx] ?? '') : '';

            // Match docente
            $cf = $this->matchDocente($docenteNome, $docenteMap);
            if (!$cf && !isset($nonMatchati[$docenteNome])) {
                $nonMatchati[$docenteNome] = true;
            }

            // Raccogli punteggi
            $punteggi = [];
            foreach ($criteriIdx as $cid => $colIdx) {
                $p = intval($row[$colIdx] ?? 0);
                if ($p >= 1 && $p <= 10) {
                    $punteggi[$cid] = $p;
                }
            }

            $media = count($punteggi) > 0 ? (int)round(array_sum($punteggi) / count($punteggi)) : 5;

            // Crea valutazione
            $val = new ValutazioneDocente();
            $val->docente_codice_fiscale = $cf;
            $val->docente_nome = $docenteNome;
            $val->studente_email = $studenteEmail;
            $val->studente_nome = $studenteNome;
            $val->sorgente_tipo = 'import_csv';
            $val->sorgente_url = $url;
            $val->note = $note;
            $val->data_valutazione = $data;
            $val->modulo_id = null;
            $val->punteggio_globale = $media;
            $val->punteggio_competenza = $media;
            $val->punteggio_chiarezza = $media;
            $val->punteggio_disponibilita = $media;
            $val->punteggio_materiali = $media;

            if ($val->save(false)) {
                foreach ($punteggi as $cid => $p) {
                    $punt = new ValutazioneDocentePunteggio();
                    $punt->valutazione_docente_id = $val->id;
                    $punt->criterio_id = $cid;
                    $punt->punteggio = $p;
                    $punt->save(false);
                }
                $importate++;
            } else {
                $errori++;
            }
        }

        fclose($handle);
        return ['importate' => $importate, 'errori' => $errori];
    }

    private function matchDocente($nome, &$map)
    {
        $n = mb_strtolower(trim($nome));
        if (isset($map[$n])) return $map[$n];

        // Contenimento
        foreach ($map as $k => $cf) {
            if (strlen($k) > 3 && (str_contains($k, $n) || str_contains($n, $k))) return $cf;
        }

        // Levenshtein
        $best = 999;
        $bcf = null;
        foreach ($map as $k => $cf) {
            if (abs(strlen($k) - strlen($n)) > 5) continue;
            $d = levenshtein($n, $k);
            if ($d < $best && $d <= 3) {
                $best = $d;
                $bcf = $cf;
            }
        }
        return $bcf;
    }

    private function parseDate($dateStr)
    {
        $dateStr = trim($dateStr);
        $cleaned = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $dateStr);
        $ts = strtotime($cleaned);
        return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
    }
}
