<?php

namespace app\controllers;

use Yii;
use app\models\Progetto;
use app\models\MovimentoSpesa;
use app\models\TrancheFinanziamento;
use app\models\VoceSpesaCategoria;
use app\models\VoceSpesa;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * BilancioController - Dashboard bilancio e quadro economico.
 */
class BilancioController extends Controller
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
        ];
    }

    /**
     * Dashboard bilancio generale - tutti i progetti.
     * @return string
     */
    public function actionIndex()
    {
        $progetti = Progetto::find()
            ->orderBy(['codice_progetto' => SORT_ASC])
            ->all();

        $riepilogo = [];
        $totaleEntrate = 0;
        $totaleUscite = 0;
        $totaleBudget = 0;

        foreach ($progetti as $progetto) {
            $entrate = TrancheFinanziamento::getTotaleAccreditato($progetto->id);
            $uscite = MovimentoSpesa::getTotaleSpese($progetto->id);
            $budget = (float) $progetto->budget_totale;

            $riepilogo[] = [
                'progetto' => $progetto,
                'budget' => $budget,
                'entrate' => $entrate,
                'uscite' => $uscite,
                'saldo' => $entrate - $uscite,
                'percentuale_speso' => $budget > 0 ? round(($uscite / $budget) * 100, 1) : 0,
            ];

            $totaleEntrate += $entrate;
            $totaleUscite += $uscite;
            $totaleBudget += $budget;
        }

        return $this->render('index', [
            'riepilogo' => $riepilogo,
            'totaleEntrate' => $totaleEntrate,
            'totaleUscite' => $totaleUscite,
            'totaleBudget' => $totaleBudget,
        ]);
    }

    /**
     * Dettaglio bilancio singolo progetto.
     * @param int $id Progetto ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProgetto($id)
    {
        $progetto = Progetto::findOne($id);
        if (!$progetto) {
            throw new NotFoundHttpException('Progetto non trovato.');
        }

        $budget = (float) $progetto->budget_totale;
        $totaleSpese = MovimentoSpesa::getTotaleSpese($id);
        $totaleEntrate = MovimentoSpesa::getTotaleEntrate($id);
        $trancheAccreditate = TrancheFinanziamento::getTotaleAccreditato($id);
        $tranchePreviste = TrancheFinanziamento::getTotalePrevisto($id);

        // Movimenti raggruppati per categoria
        $movimentiPerCategoria = [];
        $movimenti = MovimentoSpesa::find()
            ->alias('ms')
            ->leftJoin('voce_spesa vs', 'ms.voce_spesa_id = vs.id')
            ->leftJoin('voce_spesa_categoria vsc', 'vs.categoria_id = vsc.id')
            ->where(['ms.progetto_id' => $id, 'ms.deleted_at' => null, 'ms.tipo' => MovimentoSpesa::TIPO_SPESA])
            ->orderBy(['vsc.ordinamento' => SORT_ASC, 'vs.ordinamento' => SORT_ASC, 'ms.data_movimento' => SORT_DESC])
            ->all();

        foreach ($movimenti as $mov) {
            $voce = $mov->voceSpesa;
            $cat = $voce ? $voce->categoria : null;
            $catLabel = $cat ? $cat->getLabel() : 'Senza categoria';
            $movimentiPerCategoria[$catLabel][] = $mov;
        }

        // Tranche
        $tranche = TrancheFinanziamento::find()
            ->where(['progetto_id' => $id])
            ->orderBy(['numero_tranche' => SORT_ASC])
            ->all();

        return $this->render('progetto', [
            'progetto' => $progetto,
            'budget' => $budget,
            'totaleSpese' => $totaleSpese,
            'totaleEntrate' => $totaleEntrate,
            'trancheAccreditate' => $trancheAccreditate,
            'tranchePreviste' => $tranchePreviste,
            'movimentiPerCategoria' => $movimentiPerCategoria,
            'tranche' => $tranche,
        ]);
    }

    /**
     * Quadro economico - formato Bilancio Preventivo Regione.
     * @param int $id Progetto ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionQuadroEconomico($id)
    {
        $progetto = Progetto::findOne($id);
        if (!$progetto) {
            throw new NotFoundHttpException('Progetto non trovato.');
        }

        // Categorie con voci
        $categorie = VoceSpesaCategoria::find()
            ->with('vociSpesa')
            ->orderBy(['ordinamento' => SORT_ASC])
            ->all();

        // Avvisi del progetto (colonne del quadro)
        $avvisi = $progetto->avvisi;

        // Totali per voce/avviso: prepara matrice [voce_id][avviso_id] = totale
        $totaliMatrice = [];
        $totaliVoce = [];
        $totaliAvviso = [];
        $totaleGenerale = 0;

        // Movimenti per voce e per avviso (avviso tramite modulo)
        $sql = "SELECT ms.voce_spesa_id, a.id as avviso_id, SUM(ms.importo) as totale
                FROM movimento_spesa ms
                LEFT JOIN voce_spesa vs ON ms.voce_spesa_id = vs.id
                LEFT JOIN progetto p ON ms.progetto_id = p.id
                LEFT JOIN avviso a ON a.progetto_id = p.id
                WHERE ms.progetto_id = :progetto_id
                  AND ms.tipo = 'spesa'
                  AND ms.deleted_at IS NULL
                GROUP BY ms.voce_spesa_id, a.id";

        // Alternativa semplificata: totali per voce senza split per avviso
        $totaliPerVoce = MovimentoSpesa::find()
            ->alias('ms')
            ->select(['ms.voce_spesa_id', 'SUM(ms.importo) as totale'])
            ->where(['ms.progetto_id' => $id, 'ms.tipo' => MovimentoSpesa::TIPO_SPESA, 'ms.deleted_at' => null])
            ->groupBy('ms.voce_spesa_id')
            ->asArray()
            ->all();

        foreach ($totaliPerVoce as $row) {
            $totaliVoce[$row['voce_spesa_id']] = (float) $row['totale'];
            $totaleGenerale += (float) $row['totale'];
        }

        // Totali per categoria
        $totaliCategoria = [];
        foreach ($categorie as $cat) {
            $subtotale = 0;
            foreach ($cat->vociSpesa as $voce) {
                $subtotale += $totaliVoce[$voce->id] ?? 0;
            }
            $totaliCategoria[$cat->id] = $subtotale;
        }

        // Totali per sezione
        $totaliSezione = [];
        foreach ($categorie as $cat) {
            if (!isset($totaliSezione[$cat->sezione])) {
                $totaliSezione[$cat->sezione] = 0;
            }
            $totaliSezione[$cat->sezione] += $totaliCategoria[$cat->id];
        }

        return $this->render('quadro-economico', [
            'progetto' => $progetto,
            'categorie' => $categorie,
            'totaliVoce' => $totaliVoce,
            'totaliCategoria' => $totaliCategoria,
            'totaliSezione' => $totaliSezione,
            'totaleGenerale' => $totaleGenerale,
            'budget' => (float) $progetto->budget_totale,
        ]);
    }

    /**
     * Export Excel del quadro economico.
     * @param int $id Progetto ID
     * @throws NotFoundHttpException
     */
    public function actionExport($id)
    {
        $progetto = Progetto::findOne($id);
        if (!$progetto) {
            throw new NotFoundHttpException('Progetto non trovato.');
        }

        $categorie = VoceSpesaCategoria::find()
            ->with('vociSpesa')
            ->orderBy(['ordinamento' => SORT_ASC])
            ->all();

        $totaliPerVoce = MovimentoSpesa::find()
            ->alias('ms')
            ->select(['ms.voce_spesa_id', 'SUM(ms.importo) as totale'])
            ->where(['ms.progetto_id' => $id, 'ms.tipo' => MovimentoSpesa::TIPO_SPESA, 'ms.deleted_at' => null])
            ->groupBy('ms.voce_spesa_id')
            ->asArray()
            ->all();

        $totaliVoce = [];
        foreach ($totaliPerVoce as $row) {
            $totaliVoce[$row['voce_spesa_id']] = (float) $row['totale'];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Quadro Economico');

        // Stili
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '206BC4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $categoryStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F0FE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $totalStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4EDDA']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // Intestazione
        $sheet->setCellValue('A1', 'QUADRO ECONOMICO - ' . $progetto->codice_progetto);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A2', $progetto->titolo);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header colonne
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Codice');
        $sheet->setCellValue('B' . $row, 'Voce di Spesa');
        $sheet->setCellValue('C' . $row, 'Budget Preventivo');
        $sheet->setCellValue('D' . $row, 'Speso');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($headerStyle);

        $row++;

        $sezioneLabels = VoceSpesaCategoria::getSezioneList();
        $currentSezione = null;
        $totaleGenerale = 0;

        foreach ($categorie as $cat) {
            // Intestazione sezione
            if ($cat->sezione !== $currentSezione) {
                $currentSezione = $cat->sezione;
                $sheet->setCellValue('A' . $row, strtoupper($sezioneLabels[$currentSezione] ?? $currentSezione));
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '206BC4']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F5FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $row++;
            }

            // Intestazione categoria
            $subtotale = 0;
            $sheet->setCellValue('A' . $row, $cat->codice);
            $sheet->setCellValue('B' . $row, $cat->nome);
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($categoryStyle);
            $catRow = $row;
            $row++;

            // Voci
            foreach ($cat->vociSpesa as $voce) {
                $importo = $totaliVoce[$voce->id] ?? 0;
                $subtotale += $importo;

                $sheet->setCellValue('A' . $row, $voce->codice);
                $sheet->setCellValue('B' . $row, $voce->nome);
                $sheet->setCellValue('C' . $row, '');
                $sheet->setCellValue('D' . $row, $importo);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $row++;
            }

            // Subtotale categoria
            $sheet->setCellValue('D' . $catRow, $subtotale);
            $sheet->getStyle("D{$catRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $totaleGenerale += $subtotale;
        }

        // Totale Generale
        $row++;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'TOTALE GENERALE');
        $sheet->setCellValue('C' . $row, (float) $progetto->budget_totale);
        $sheet->setCellValue('D' . $row, $totaleGenerale);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($totalStyle);
        $sheet->getStyle("C{$row}:D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // Larghezze colonne
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);

        // Genera file
        $fileName = 'quadro_economico_' . $progetto->codice_progetto . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
