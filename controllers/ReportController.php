<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Query;
use app\models\Progetto;
use app\models\Docente;
use app\models\Modulo;
use app\models\Contratto;
use app\models\Pagamento;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * ReportController - Statistiche e Export dati
 */
class ReportController extends Controller
{
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
                'actions' => [],
            ],
        ];
    }

    /**
     * Statistiche generali con grafici
     */
    public function actionStatistiche()
    {
        // 1. Progetti per fonte finanziamento
        $progettiPerFonte = (new Query())
            ->select(['ff.codice AS fonte', 'COUNT(p.id) AS totale'])
            ->from('progetto p')
            ->leftJoin('fonte_finanziamento ff', 'ff.id = p.fonte_finanziamento_id AND ff.deleted_at IS NULL')
            ->where(['p.deleted_at' => null])
            ->groupBy('p.fonte_finanziamento_id')
            ->orderBy(['totale' => SORT_DESC])
            ->all();

        // 2. Budget per progetto (top 10 per contrattualizzato)
        $budgetPerProgetto = (new Query())
            ->select([
                'p.codice_progetto',
                'COALESCE(SUM(c.compenso_totale), 0) AS contrattualizzato',
                'COALESCE(SUM(CASE WHEN pag.status = \'pagato\' THEN pag.importo_netto ELSE 0 END), 0) AS pagato',
            ])
            ->from('progetto p')
            ->leftJoin('avviso a', 'a.progetto_id = p.id AND a.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.avviso_id = a.id AND m.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL')
            ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
            ->leftJoin('pagamento pag', 'pag.contratto_id = c.id AND pag.deleted_at IS NULL')
            ->where(['p.deleted_at' => null])
            ->groupBy(['p.id', 'p.codice_progetto'])
            ->orderBy(['contrattualizzato' => SORT_DESC])
            ->limit(10)
            ->all();

        // 3. Pagamenti mensili ultimi 12 mesi
        $pagamentiMensili = (new Query())
            ->select([
                'YEAR(pag.data_pagamento) AS anno',
                'MONTH(pag.data_pagamento) AS mese',
                'COALESCE(SUM(pag.importo_netto), 0) AS totale',
            ])
            ->from('pagamento pag')
            ->where(['pag.deleted_at' => null])
            ->andWhere(['pag.status' => 'pagato'])
            ->andWhere(['>=', 'pag.data_pagamento', date('Y-m-d', strtotime('-12 months'))])
            ->groupBy(['YEAR(pag.data_pagamento)', 'MONTH(pag.data_pagamento)'])
            ->orderBy(['anno' => SORT_ASC, 'mese' => SORT_ASC])
            ->all();

        // 4. KPI
        $docentiAttivi = (int)(new Query())
            ->from('docente')
            ->where(['deleted_at' => null])
            ->count();

        $oreErogate = (float)(new Query())
            ->from('rendicontazione_ore')
            ->where(['deleted_at' => null])
            ->andWhere(['status' => 'approvato'])
            ->sum('ore') ?: 0;

        $moduliTotali = (int)(new Query())
            ->from('modulo')
            ->where(['deleted_at' => null])
            ->count();

        $moduliConAssegnazione = (int)(new Query())
            ->select('COUNT(DISTINCT m.id)')
            ->from('modulo m')
            ->innerJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL')
            ->where(['m.deleted_at' => null])
            ->scalar();

        $percCompletamento = $moduliTotali > 0 ? round(($moduliConAssegnazione / $moduliTotali) * 100, 1) : 0;

        $totaleContrattualizzato = (float)(new Query())
            ->from('contratto')
            ->where(['deleted_at' => null])
            ->sum('compenso_totale') ?: 0;

        return $this->render('statistiche', [
            'progettiPerFonte' => $progettiPerFonte,
            'budgetPerProgetto' => $budgetPerProgetto,
            'pagamentiMensili' => $pagamentiMensili,
            'docentiAttivi' => $docentiAttivi,
            'oreErogate' => $oreErogate,
            'totaleContrattualizzato' => $totaleContrattualizzato,
            'percCompletamento' => $percCompletamento,
        ]);
    }

    /**
     * Pagina selezione export
     */
    public function actionExport()
    {
        $counts = [
            'docenti' => (int)Docente::find()->count(),
            'pagamenti' => (int)Pagamento::find()->count(),
            'contratti' => (int)Contratto::find()->count(),
            'moduli' => (int)Modulo::find()->count(),
            'progetti' => (int)Progetto::find()->count(),
        ];

        // Lista progetti per export consulente
        $progetti = Progetto::find()
            ->orderBy(['titolo' => SORT_ASC])
            ->all();

        return $this->render('export', [
            'counts' => $counts,
            'progetti' => $progetti,
        ]);
    }

    /**
     * Export CSV
     */
    public function actionExportCsv($entity)
    {
        $data = $this->getExportData($entity);
        if ($data === false) {
            Yii::$app->session->setFlash('error', 'Entita non supportata.');
            return $this->redirect(['export']);
        }

        $filename = $entity . '_' . date('Y-m-d_His') . '.csv';

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $handle = fopen('php://temp', 'r+');

        // BOM UTF-8 per Excel
        fwrite($handle, "\xEF\xBB\xBF");

        // Header
        if (!empty($data['rows'])) {
            fputcsv($handle, $data['headers'], ';');
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row, ';');
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response->content = $content;
        return $response;
    }

    /**
     * Export Excel
     */
    public function actionExportExcel($entity)
    {
        $data = $this->getExportData($entity);
        if ($data === false) {
            Yii::$app->session->setFlash('error', 'Entita non supportata.');
            return $this->redirect(['export']);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($entity));

        // Header bold
        $col = 1;
        foreach ($data['headers'] as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $col++;
        }

        // Dati
        $row = 2;
        foreach ($data['rows'] as $rowData) {
            $col = 1;
            foreach ($rowData as $value) {
                $sheet->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-width
        foreach (range(1, count($data['headers'])) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }

        $filename = $entity . '_' . date('Y-m-d_His') . '.xlsx';

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $response->content = ob_get_clean();

        return $response;
    }

    /**
     * Export Excel per consulente del lavoro - dati completi docenti con contratto per progetto
     */
    public function actionExportConsulente($progetto_id)
    {
        $progetto = Progetto::findOne($progetto_id);
        if (!$progetto) {
            throw new NotFoundHttpException('Progetto non trovato.');
        }

        // Query con leftJoin chain: progetto → avviso → modulo → assegnazione → contratto + docente + sede
        $rows = (new Query())
            ->select([
                'd.cognome',
                'd.nome',
                'd.codice_fiscale',
                'd.data_nascita',
                'd.luogo_nascita',
                'd.indirizzo',
                'd.cap',
                'd.citta',
                'd.provincia',
                'd.email',
                'd.telefono',
                'd.titolo_studio',
                'd.provenienza',
                'p.titolo AS nome_progetto',
                'm.titolo AS nome_modulo',
                's.nome AS sede_nome',
                'c.ore_contrattualizzate',
                'c.compenso_orario',
                'c.compenso_totale',
                'c.tipo AS tipo_contratto',
                'c.data_inizio_lezioni',
                'c.data_fine_lezioni',
            ])
            ->from('progetto p')
            ->leftJoin('avviso a', 'a.progetto_id = p.id AND a.deleted_at IS NULL')
            ->leftJoin('modulo m', 'm.avviso_id = a.id AND m.deleted_at IS NULL')
            ->leftJoin('sede s', 's.id = m.sede_id AND s.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.deleted_at IS NULL')
            ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
            ->leftJoin('docente d', 'd.codice_fiscale = ass.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->where(['p.id' => $progetto_id])
            ->andWhere(['p.deleted_at' => null])
            ->andWhere(['IS NOT', 'c.id', null]) // Solo assegnazioni con contratto
            ->orderBy(['d.cognome' => SORT_ASC, 'd.nome' => SORT_ASC, 'm.titolo' => SORT_ASC])
            ->all();

        if (empty($rows)) {
            Yii::$app->session->setFlash('warning', 'Nessun contratto trovato per questo progetto.');
            return $this->redirect(['/progetto/view', 'id' => $progetto_id]);
        }

        // Mappa tipo contratto
        $tipoContrattoLabels = Contratto::getTipoList();

        // Crea Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consulente Lavoro');

        // Headers
        $headers = [
            'Cognome', 'Nome', 'Codice Fiscale', 'Data Nascita', 'Luogo Nascita',
            'Indirizzo', 'CAP', 'Citta', 'Provincia', 'Email', 'Telefono',
            'Titolo Studio', 'Provenienza', 'Nome Progetto', 'Nome Modulo', 'Sede',
            'Ore Contrattualizzate', 'Compenso Orario (EUR)', 'Compenso Totale (EUR)',
            'Tipo Contratto', 'Data Inizio Lezioni', 'Data Fine Lezioni',
        ];

        // Scrivi headers
        foreach ($headers as $colIdx => $header) {
            $cell = $sheet->getCellByColumnAndRow($colIdx + 1, 1);
            $cell->setValue($header);
        }

        // Stile header: sfondo blu (#206bc4), testo bianco, grassetto
        $headerRange = 'A1:V1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '206BC4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Scrivi dati
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->setCellValueByColumnAndRow(1, $rowNum, $row['cognome']);
            $sheet->setCellValueByColumnAndRow(2, $rowNum, $row['nome']);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $row['codice_fiscale']);
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $row['data_nascita']);
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $row['luogo_nascita']);
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $row['indirizzo']);
            $sheet->setCellValueByColumnAndRow(7, $rowNum, $row['cap']);
            $sheet->setCellValueByColumnAndRow(8, $rowNum, $row['citta']);
            $sheet->setCellValueByColumnAndRow(9, $rowNum, $row['provincia']);
            $sheet->setCellValueByColumnAndRow(10, $rowNum, $row['email']);
            $sheet->setCellValueByColumnAndRow(11, $rowNum, $row['telefono']);
            $sheet->setCellValueByColumnAndRow(12, $rowNum, $row['titolo_studio']);
            $sheet->setCellValueByColumnAndRow(13, $rowNum, $row['provenienza']);
            $sheet->setCellValueByColumnAndRow(14, $rowNum, $row['nome_progetto']);
            $sheet->setCellValueByColumnAndRow(15, $rowNum, $row['nome_modulo']);
            $sheet->setCellValueByColumnAndRow(16, $rowNum, $row['sede_nome']);
            $sheet->setCellValueByColumnAndRow(17, $rowNum, (int)$row['ore_contrattualizzate']);
            $sheet->setCellValueByColumnAndRow(18, $rowNum, (float)$row['compenso_orario']);
            $sheet->setCellValueByColumnAndRow(19, $rowNum, (float)$row['compenso_totale']);
            $sheet->setCellValueByColumnAndRow(20, $rowNum, $tipoContrattoLabels[$row['tipo_contratto']] ?? $row['tipo_contratto']);
            $sheet->setCellValueByColumnAndRow(21, $rowNum, $row['data_inizio_lezioni']);
            $sheet->setCellValueByColumnAndRow(22, $rowNum, $row['data_fine_lezioni']);

            $rowNum++;
        }

        // Formato numeri EUR con 2 decimali per colonne R e S (18 e 19)
        $lastRow = $rowNum - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("R2:R{$lastRow}")->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("S2:S{$lastRow}")->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        // Auto-dimensionamento colonne
        foreach (range(1, count($headers)) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }

        // Genera nome file sicuro
        $titoloSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $progetto->titolo);
        $titoloSafe = substr($titoloSafe, 0, 50);
        $filename = 'Consulente_' . $titoloSafe . '_' . date('Y-m-d') . '.xlsx';

        // Invia come download
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $response->content = ob_get_clean();

        return $response;
    }

    /**
     * Report docenti con lezioni in calendario ma senza contratto
     */
    public function actionDocentiDaContrattualizzare($progetto_id = null)
    {
        // Query: docenti con lezioni in calendario MA senza contratto
        $query = (new Query())
            ->select([
                'd.codice_fiscale',
                'd.cognome',
                'd.nome',
                'd.provenienza',
                'd.email',
                'd.telefono',
                'm.id AS modulo_id',
                'm.codice AS modulo_codice',
                'm.titolo AS modulo_titolo',
                'm.compenso_orario',
                'p.id AS progetto_id',
                'p.titolo AS progetto_titolo',
                'p.codice_progetto',
                'COUNT(cal.id) AS num_lezioni',
                'SUM(cal.ore) AS ore_totali',
            ])
            ->from('calendario cal')
            ->innerJoin('docente d', 'd.codice_fiscale = cal.docente_codice_fiscale AND d.deleted_at IS NULL')
            ->innerJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
            ->innerJoin('avviso a', 'a.id = m.avviso_id AND a.deleted_at IS NULL')
            ->innerJoin('progetto p', 'p.id = a.progetto_id AND p.deleted_at IS NULL')
            ->leftJoin('assegnazione ass', 'ass.modulo_id = m.id AND ass.docente_codice_fiscale = d.codice_fiscale AND ass.deleted_at IS NULL')
            ->leftJoin('contratto c', 'c.assegnazione_id = ass.id AND c.deleted_at IS NULL')
            ->where(['c.id' => null])
            ->andWhere(['cal.deleted_at' => null])
            ->andWhere(['cal.status' => ['programmata', 'completata']])
            ->groupBy(['d.codice_fiscale', 'm.id', 'p.id']);

        if ($progetto_id) {
            $query->andWhere(['p.id' => $progetto_id]);
        }

        $query->orderBy(['p.titolo' => SORT_ASC, 'd.cognome' => SORT_ASC, 'm.codice' => SORT_ASC]);

        $docenti = $query->all();

        // Lista progetti per filtro dropdown
        $progetti = Progetto::find()
            ->orderBy(['titolo' => SORT_ASC])
            ->all();

        return $this->render('docenti-da-contrattualizzare', [
            'docenti' => $docenti,
            'progetti' => $progetti,
            'progetto_id' => $progetto_id,
        ]);
    }

    /**
     * Prepara i dati per export in base all'entita
     */
    private function getExportData($entity)
    {
        switch ($entity) {
            case 'docenti':
                $models = Docente::find()->orderBy('cognome, nome')->all();
                return [
                    'headers' => ['Codice Fiscale', 'Cognome', 'Nome', 'Email', 'Telefono', 'Provenienza', 'Citta', 'Titolo Studio'],
                    'rows' => array_map(function ($m) {
                        return [
                            $m->codice_fiscale,
                            $m->cognome,
                            $m->nome,
                            $m->email,
                            $m->telefono,
                            $m->provenienza,
                            $m->citta,
                            $m->titolo_studio,
                        ];
                    }, $models),
                ];

            case 'pagamenti':
                $models = Pagamento::find()
                    ->alias('pag')
                    ->select(['pag.*', 'c.numero_contratto', 'c.compenso_totale'])
                    ->leftJoin('contratto c', 'c.id = pag.contratto_id AND c.deleted_at IS NULL')
                    ->orderBy(['pag.data_pagamento' => SORT_DESC])
                    ->all();
                return [
                    'headers' => ['ID', 'Contratto', 'Importo Lordo', 'Ritenute', 'Importo Netto', 'Data Pagamento', 'Tipo', 'Status', 'Rif. Fattura', 'N. Mandato'],
                    'rows' => array_map(function ($m) {
                        return [
                            $m->id,
                            $m->contratto ? $m->contratto->numero_contratto : $m->contratto_id,
                            $m->importo_lordo,
                            $m->ritenute,
                            $m->importo_netto,
                            $m->data_pagamento,
                            $m->tipo,
                            $m->status,
                            $m->riferimento_fattura,
                            $m->numero_mandato,
                        ];
                    }, $models),
                ];

            case 'contratti':
                $models = Contratto::find()->orderBy(['data_stipula' => SORT_DESC])->all();
                return [
                    'headers' => ['ID', 'N. Contratto', 'Tipo', 'Data Stipula', 'Data Inizio', 'Data Fine', 'Compenso Orario', 'Ore', 'Compenso Totale', 'Status'],
                    'rows' => array_map(function ($m) {
                        return [
                            $m->id,
                            $m->numero_contratto,
                            $m->tipo,
                            $m->data_stipula,
                            $m->data_inizio,
                            $m->data_fine,
                            $m->compenso_orario,
                            $m->ore_contrattualizzate,
                            $m->compenso_totale,
                            $m->status,
                        ];
                    }, $models),
                ];

            case 'moduli':
                $models = Modulo::find()->orderBy('codice')->all();
                return [
                    'headers' => ['ID', 'Codice', 'Titolo', 'Ore Totali', 'Compenso Orario', 'Data Inizio', 'Data Fine', 'Status', 'Filiera'],
                    'rows' => array_map(function ($m) {
                        return [
                            $m->id,
                            $m->codice,
                            $m->titolo,
                            $m->ore_totali,
                            $m->compenso_orario,
                            $m->data_inizio,
                            $m->data_fine,
                            $m->status,
                            $m->filiera,
                        ];
                    }, $models),
                ];

            case 'progetti':
                $models = Progetto::find()->orderBy('codice_progetto')->all();
                return [
                    'headers' => ['ID', 'Codice', 'Titolo', 'Budget Totale', 'Data Inizio', 'Data Fine', 'Status', 'N. Studenti'],
                    'rows' => array_map(function ($m) {
                        return [
                            $m->id,
                            $m->codice_progetto,
                            $m->titolo,
                            $m->budget_totale,
                            $m->data_inizio,
                            $m->data_fine,
                            $m->status,
                            $m->numero_studenti,
                        ];
                    }, $models),
                ];

            default:
                return false;
        }
    }
}
