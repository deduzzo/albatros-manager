<?php

namespace app\controllers;

use Yii;
use app\models\BorsaStudio;
use app\models\BorsaStudioSearch;
use app\models\Progetto;
use app\models\Allievo;
use app\models\Iscrizione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * BorsaStudioController - Gestione borse di studio PNRR
 */
class BorsaStudioController extends Controller
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
                    'revoca' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista borse di studio con stat cards
     */
    public function actionIndex()
    {
        $searchModel = new BorsaStudioSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Stat cards
        $query = BorsaStudio::find();
        $stats = [
            'attive' => (int) (clone $query)->andWhere(['in', 'status', [
                BorsaStudio::STATUS_APPROVATA,
                BorsaStudio::STATUS_IN_EROGAZIONE,
            ]])->count(),
            'importo_totale' => (float) (clone $query)->andWhere(['not', ['status' => BorsaStudio::STATUS_REVOCATA]])->sum('importo_finale') ?: 0,
            'erogato' => (float) (clone $query)->sum('totale_erogato') ?: 0,
        ];
        $stats['da_erogare'] = $stats['importo_totale'] - $stats['erogato'];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Dettaglio borsa di studio con timeline erogazioni
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creazione nuova borsa di studio
     */
    public function actionCreate()
    {
        $model = new BorsaStudio();
        $model->status = BorsaStudio::STATUS_RICHIESTA;

        if ($model->load(Yii::$app->request->post())) {
            // Calcola importi
            $isStudentessa = Yii::$app->request->post('is_studentessa', 0);
            $isDisabile = Yii::$app->request->post('is_disabile', 0);
            $model->calcolaImporto((bool) $isStudentessa, (bool) $isDisabile);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Borsa di studio creata con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'progetti' => self::getProgettiList(),
        ]);
    }

    /**
     * Modifica borsa di studio (solo se status=richiesta)
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== BorsaStudio::STATUS_RICHIESTA) {
            Yii::$app->session->setFlash('error', 'La borsa puo essere modificata solo in stato "Richiesta".');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $isStudentessa = Yii::$app->request->post('is_studentessa', 0);
            $isDisabile = Yii::$app->request->post('is_disabile', 0);
            $model->calcolaImporto((bool) $isStudentessa, (bool) $isDisabile);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Borsa di studio aggiornata con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'progetti' => self::getProgettiList(),
        ]);
    }

    /**
     * Soft delete borsa di studio
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Borsa di studio eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Erogazione trimestre
     */
    public function actionEroga($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->status, [BorsaStudio::STATUS_APPROVATA, BorsaStudio::STATUS_IN_EROGAZIONE])) {
            Yii::$app->session->setFlash('error', 'La borsa deve essere in stato "Approvata" o "In Erogazione" per poter erogare.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost) {
            $trimestre = (int) Yii::$app->request->post('trimestre');
            $importo = (float) Yii::$app->request->post('importo');
            $dataErogazione = Yii::$app->request->post('data_erogazione');
            $note = Yii::$app->request->post('note_erogazione', '');

            if ($trimestre < 1 || $trimestre > 4) {
                Yii::$app->session->setFlash('error', 'Trimestre non valido.');
                return $this->redirect(['eroga', 'id' => $model->id]);
            }

            $campo = 'importo_q' . $trimestre;
            if ($model->$campo > 0) {
                Yii::$app->session->setFlash('error', 'Il trimestre Q' . $trimestre . ' e gia stato erogato.');
                return $this->redirect(['eroga', 'id' => $model->id]);
            }

            if ($model->erogaTrimestre($trimestre, $importo)) {
                $nota = "Erogato Q{$trimestre}: " . Yii::$app->formatter->asCurrency($importo, 'EUR');
                if ($dataErogazione) {
                    $nota .= " in data {$dataErogazione}";
                }
                if ($note) {
                    $nota .= " - {$note}";
                }
                $model->withAuditNote($nota)->save(false);

                Yii::$app->session->setFlash('success', 'Trimestre Q' . $trimestre . ' erogato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Errore durante l\'erogazione.');
            }
        }

        return $this->render('eroga', [
            'model' => $model,
        ]);
    }

    /**
     * Revoca borsa di studio con motivazione obbligatoria
     */
    public function actionRevoca($id)
    {
        $model = $this->findModel($id);

        if ($model->status === BorsaStudio::STATUS_REVOCATA) {
            Yii::$app->session->setFlash('warning', 'La borsa e gia stata revocata.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost) {
            $motivo = Yii::$app->request->post('motivo_revoca', '');

            if (empty(trim($motivo))) {
                Yii::$app->session->setFlash('error', 'Il motivo della revoca e obbligatorio.');
                return $this->render('revoca', ['model' => $model]);
            }

            $model->status = BorsaStudio::STATUS_REVOCATA;
            $model->motivo_revoca = $motivo;

            if ($model->withAuditNote('Borsa revocata: ' . $motivo)->save()) {
                Yii::$app->session->setFlash('success', 'Borsa di studio revocata.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Errore durante la revoca.');
            }
        }

        return $this->render('revoca', [
            'model' => $model,
        ]);
    }

    /**
     * Export Excel beneficiari
     */
    public function actionExport()
    {
        $borse = BorsaStudio::find()
            ->with(['allievo', 'progetto'])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Borse di Studio');

        // Headers
        $headers = [
            'A1' => 'Studente',
            'B1' => 'Codice Fiscale',
            'C1' => 'Progetto',
            'D1' => 'Anno Formativo',
            'E1' => 'Tipologia Sede',
            'F1' => 'ISEE',
            'G1' => 'ISPE',
            'H1' => 'Importo Base',
            'I1' => 'Maggiorazione',
            'J1' => 'Importo Finale',
            'K1' => 'Erogato',
            'L1' => 'Residuo',
            'M1' => 'Stato',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Stile header: sfondo blu, testo bianco, bold
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '206bc4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Dati
        $row = 2;
        foreach ($borse as $borsa) {
            $nomeStudente = $borsa->allievo ? $borsa->allievo->getNomeCompleto() : '-';
            $nomeProgetto = $borsa->progetto ? $borsa->progetto->titolo : '-';
            $tipologiaLabel = $borsa->getTipologiaSedeLabel();
            $statusLabel = $borsa->getStatusLabel();

            $sheet->setCellValue("A{$row}", $nomeStudente);
            $sheet->setCellValue("B{$row}", $borsa->allievo_codice_fiscale);
            $sheet->setCellValue("C{$row}", $nomeProgetto);
            $sheet->setCellValue("D{$row}", $borsa->anno_formativo);
            $sheet->setCellValue("E{$row}", $tipologiaLabel);
            $sheet->setCellValue("F{$row}", $borsa->isee_importo);
            $sheet->setCellValue("G{$row}", $borsa->ispe_importo);
            $sheet->setCellValue("H{$row}", $borsa->importo_base);
            $sheet->setCellValue("I{$row}", $borsa->maggiorazione_percentuale ? $borsa->maggiorazione_percentuale . '%' : '-');
            $sheet->setCellValue("J{$row}", $borsa->importo_finale);
            $sheet->setCellValue("K{$row}", $borsa->totale_erogato);
            $sheet->setCellValue("L{$row}", $borsa->getResiduoDaErogare());
            $sheet->setCellValue("M{$row}", $statusLabel);

            $row++;
        }

        // Formato valuta per colonne F, G, H, J, K, L
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            foreach (['F', 'G', 'H', 'J', 'K', 'L'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
            }
        }

        // Auto-size colonne
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'borse_studio_' . date('Ymd_His') . '.xlsx';
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $response->content = ob_get_clean();

        return $response;
    }

    /**
     * Ricalcola le frequenze per tutte le borse approvate/bloccate
     */
    public function actionRicalcolaFrequenze($progetto_id = null)
    {
        $query = BorsaStudio::find()->where(['in', 'status', [
            BorsaStudio::STATUS_APPROVATA,
            BorsaStudio::STATUS_EROGAZIONE_BLOCCATA,
        ]]);
        if ($progetto_id) {
            $query->andWhere(['progetto_id' => $progetto_id]);
        }
        $borse = $query->all();
        $count = 0;
        foreach ($borse as $borsa) {
            $borsa->ricalcolaFrequenza();
            $count++;
        }
        Yii::$app->session->setFlash('success', "Frequenze ricalcolate per {$count} borse di studio.");
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Export SEPA XML pain.001 per pagamento massivo borse di studio
     */
    public function actionExportSepa($progetto_id = null)
    {
        $query = BorsaStudio::find()
            ->where(['status' => BorsaStudio::STATUS_APPROVATA])
            ->andWhere(['not', ['iban_studente' => null]])
            ->andWhere(['!=', 'iban_studente', ''])
            ->with(['allievo']);
        if ($progetto_id) {
            $query->andWhere(['progetto_id' => $progetto_id]);
        }
        $borse = $query->all();
        if (empty($borse)) {
            Yii::$app->session->setFlash('warning', 'Nessuna borsa approvata con IBAN trovata.');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }
        $pagamenti = [];
        foreach ($borse as $borsa) {
            $allievo = $borsa->allievo;
            $pagamenti[] = [
                'beneficiario' => $allievo->cognome . ' ' . $allievo->nome,
                'iban' => $borsa->iban_studente,
                'importo' => (float)$borsa->importo_finale,
                'causale' => 'Borsa di studio A.F. ' . $borsa->anno_formativo . ' - ' . $allievo->cognome . ' ' . $allievo->nome,
            ];
        }
        $xml = \app\components\SepaXmlGenerator::genera($pagamenti);
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/xml');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="SEPA_BorseStudio_' . date('Y-m-d') . '.xml"');
        return $xml;
    }

    /**
     * AJAX: studenti iscritti a un progetto (per cascading dropdown)
     */
    public function actionStudentiProgetto($progetto_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$progetto_id) {
            return [];
        }

        // Cerca studenti iscritti al progetto tramite iscrizioni ai moduli del progetto
        $studenti = Allievo::findWithDeleted()
            ->alias('al')
            ->leftJoin('iscrizione i', 'al.codice_fiscale = i.allievo_codice_fiscale AND i.deleted_at IS NULL')
            ->leftJoin('modulo m', 'i.modulo_id = m.id AND m.deleted_at IS NULL')
            ->leftJoin('avviso av', 'm.avviso_id = av.id AND av.deleted_at IS NULL')
            ->andWhere(['av.progetto_id' => $progetto_id])
            ->andWhere(['al.deleted_at' => null])
            ->groupBy('al.codice_fiscale')
            ->orderBy('al.cognome, al.nome')
            ->all();

        $result = [];
        foreach ($studenti as $studente) {
            $result[] = [
                'id' => $studente->codice_fiscale,
                'text' => $studente->cognome . ' ' . $studente->nome . ' (' . $studente->codice_fiscale . ')',
            ];
        }

        // Se nessuno studente trovato tramite iscrizioni, restituisci tutti gli allievi
        if (empty($result)) {
            $tuttiAllievi = Allievo::find()->orderBy('cognome, nome')->all();
            foreach ($tuttiAllievi as $allievo) {
                $result[] = [
                    'id' => $allievo->codice_fiscale,
                    'text' => $allievo->cognome . ' ' . $allievo->nome . ' (' . $allievo->codice_fiscale . ')',
                ];
            }
        }

        return $result;
    }

    /**
     * Trova il model o lancia eccezione
     */
    protected function findModel($id)
    {
        if (($model = BorsaStudio::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Borsa di studio non trovata.');
    }

    /**
     * Lista progetti per dropdown
     */
    protected static function getProgettiList()
    {
        return ArrayHelper::map(
            Progetto::find()->orderBy('titolo')->all(),
            'id',
            function ($model) {
                return $model->codice_progetto . ' - ' . $model->titolo;
            }
        );
    }
}
