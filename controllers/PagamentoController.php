<?php

namespace app\controllers;

use Yii;
use app\models\Pagamento;
use app\models\PagamentoSearch;
use app\models\Contratto;
use app\models\RendicontazioneOre;
use app\models\Assegnazione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PagamentoController implementa le azioni CRUD per Pagamento.
 */
class PagamentoController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'liquida-massiva' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista tutti i pagamenti.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PagamentoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Visualizza un singolo pagamento.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuovo pagamento.
     * @param int|null $contratto_id Pre-seleziona il contratto
     * @param string|null $tipo Pre-seleziona tipo (acconto, saldo, rata, rimborso)
     * @param float|null $importo Pre-popola importo lordo
     * @param float|null $importo_netto Pre-popola importo netto
     * @return string|\yii\web\Response
     */
    public function actionCreate($contratto_id = null, $tipo = null, $importo = null, $importo_netto = null)
    {
        $model = new Pagamento();
        $model->status = Pagamento::STATUS_PROGRAMMATO;

        $contratto = null;
        if ($contratto_id) {
            $model->contratto_id = $contratto_id;

            // Auto-popola saldo rimanente se nessun importo specificato via URL
            $contratto = Contratto::findOne($contratto_id);
            if ($contratto && $importo === null) {
                $saldo = $contratto->getSaldoRimanente();
                $model->importo_lordo = $saldo;
                // Calcola ritenute proporzionali se il contratto le ha
                if ($contratto->ritenute && $contratto->compenso_totale > 0) {
                    $percRitenute = $contratto->ritenute / $contratto->compenso_totale;
                    $model->ritenute = round($saldo * $percRitenute, 2);
                    $model->importo_netto = round($saldo - $model->ritenute, 2);
                } else {
                    $model->importo_netto = $saldo;
                }
            }
        }

        // Parametri URL sovrascrivono i default
        if ($tipo !== null) {
            $model->tipo = $tipo;
        }
        if ($importo !== null) {
            $model->importo_lordo = (float) $importo;
        }
        if ($importo_netto !== null) {
            $model->importo_netto = (float) $importo_netto;
        }

        // Dati ore per alert nel form
        $infoOre = null;
        if ($model->contratto_id) {
            $contratto = $contratto ?? Contratto::findOne($model->contratto_id);
            if ($contratto && $contratto->assegnazione) {
                $ass = $contratto->assegnazione;
                $infoOre = [
                    'ore_contrattualizzate' => (int) $contratto->ore_contrattualizzate,
                    'ore_approvate' => RendicontazioneOre::getTotaleOreApprovate($ass->id),
                    'ore_inserite' => RendicontazioneOre::getTotaleOreInserite($ass->id),
                    'compenso_orario' => (float) $contratto->compenso_orario,
                ];
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Pagamento creato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
            'infoOre' => $infoOre,
        ]);
    }

    /**
     * Modifica un pagamento esistente.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Pagamento aggiornato con successo.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'contratti' => $this->getContrattiList(),
        ]);
    }

    /**
     * Elimina (soft delete) un pagamento.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Pagamento eliminato con successo.');

        return $this->redirect(['index']);
    }

    /**
     * Restituisce info contratto in JSON per AJAX auto-fill form pagamento.
     * @param int $id Contratto ID
     * @return array
     */
    public function actionContrattoInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $contratto = Contratto::findOne($id);
        if (!$contratto) {
            throw new NotFoundHttpException('Contratto non trovato.');
        }

        $assegnazione = $contratto->assegnazione;
        $docente = $assegnazione ? $assegnazione->docente : null;
        $modulo = $assegnazione ? $assegnazione->modulo : null;

        // Ore rendicontate
        $oreApprovate = $assegnazione ? RendicontazioneOre::getTotaleOreApprovate($assegnazione->id) : 0;
        $oreInserite = $assegnazione ? RendicontazioneOre::getTotaleOreInserite($assegnazione->id) : 0;

        // Calcolo ritenute percentuale dal contratto
        $ritenutePerc = ($contratto->compenso_totale > 0 && $contratto->ritenute)
            ? round(($contratto->ritenute / $contratto->compenso_totale) * 100, 2)
            : 0;

        return [
            'compenso_totale' => (float) $contratto->compenso_totale,
            'compenso_orario' => (float) $contratto->compenso_orario,
            'totale_pagato' => $contratto->getTotalePagato(),
            'saldo_rimanente' => $contratto->getSaldoRimanente(),
            'ritenute_percentuale' => $ritenutePerc,
            'netto_docente' => (float) ($contratto->netto_docente ?: 0),
            'ore_contrattualizzate' => (int) $contratto->ore_contrattualizzate,
            'ore_approvate' => (float) $oreApprovate,
            'ore_inserite' => (float) $oreInserite,
            'docente' => $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D',
            'modulo_codice' => $modulo ? $modulo->codice : 'N/D',
            'modulo_titolo' => $modulo ? $modulo->titolo : 'N/D',
        ];
    }

    /**
     * Restituisce i contratti con saldo rimanente > 0 per la liquidazione massiva.
     * @return array
     */
    public function actionContrattiConSaldo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $contratti = Contratto::find()
            ->joinWith(['assegnazione.docente', 'assegnazione.modulo'])
            ->orderBy(['contratto.id' => SORT_DESC])
            ->all();

        $result = [];
        foreach ($contratti as $contratto) {
            $saldo = $contratto->getSaldoRimanente();
            if ($saldo <= 0.01) {
                continue;
            }

            $docente = $contratto->assegnazione && $contratto->assegnazione->docente
                ? $contratto->assegnazione->docente->cognome . ' ' . $contratto->assegnazione->docente->nome
                : 'N/D';
            $modulo = $contratto->assegnazione && $contratto->assegnazione->modulo
                ? $contratto->assegnazione->modulo->codice
                : 'N/D';

            $result[] = [
                'id' => $contratto->id,
                'numero_contratto' => $contratto->numero_contratto ?: '#' . $contratto->id,
                'docente' => $docente,
                'modulo' => $modulo,
                'compenso_totale' => (float) $contratto->compenso_totale,
                'totale_pagato' => $contratto->getTotalePagato(),
                'saldo_rimanente' => round($saldo, 2),
            ];
        }

        return $result;
    }

    /**
     * Liquidazione massiva: crea pagamenti per piu' contratti.
     * POST JSON: { contratti_ids: [1,2,3], tipo: "saldo", status: "programmato" }
     * @return array
     */
    public function actionLiquidaMassiva()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $contrattiIds = $request->post('contratti_ids', []);
        $tipo = $request->post('tipo');
        $status = $request->post('status', Pagamento::STATUS_PROGRAMMATO);

        if (empty($contrattiIds) || empty($tipo)) {
            return ['success' => false, 'message' => 'Parametri mancanti: contratti_ids e tipo sono obbligatori.'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $count = 0;
            $totale = 0;
            $errori = [];

            foreach ($contrattiIds as $contrattoId) {
                $contratto = Contratto::findOne($contrattoId);
                if (!$contratto) {
                    $errori[] = "Contratto #{$contrattoId} non trovato.";
                    continue;
                }

                $saldo = $contratto->getSaldoRimanente();
                if ($saldo <= 0.01) {
                    $errori[] = "Contratto #{$contrattoId}: saldo zero o negativo.";
                    continue;
                }

                $pagamento = new Pagamento();
                $pagamento->contratto_id = $contrattoId;
                $pagamento->tipo = $tipo;
                $pagamento->status = $status;
                $pagamento->importo_lordo = $saldo;

                // Calcola ritenute proporzionali
                if ($contratto->ritenute && $contratto->compenso_totale > 0) {
                    $percRitenute = $contratto->ritenute / $contratto->compenso_totale;
                    $pagamento->ritenute = round($saldo * $percRitenute, 2);
                    $pagamento->importo_netto = round($saldo - $pagamento->ritenute, 2);
                } else {
                    $pagamento->importo_netto = $saldo;
                }

                if ($pagamento->save()) {
                    $count++;
                    $totale += $pagamento->importo_netto;
                } else {
                    $errori[] = "Contratto #{$contrattoId}: " . implode(', ', $pagamento->getFirstErrors());
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'count' => $count,
                'totale' => round($totale, 2),
                'errori' => $errori,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'Errore: ' . $e->getMessage()];
        }
    }

    /**
     * Trova il model in base all'ID.
     * @param int $id
     * @return Pagamento
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Pagamento::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Il pagamento richiesto non esiste.');
    }

    /**
     * Restituisce la lista dei contratti per le dropdown.
     * @return array
     */
    protected function getContrattiList()
    {
        return ArrayHelper::map(
            Contratto::find()
                ->joinWith(['assegnazione.docente'])
                ->orderBy('id DESC')
                ->all(),
            'id',
            function ($model) {
                $docente = $model->assegnazione && $model->assegnazione->docente
                    ? $model->assegnazione->docente->cognome . ' ' . $model->assegnazione->docente->nome
                    : 'N/D';
                $numero = $model->numero_contratto ?: 'N. ' . $model->id;
                return $numero . ' - ' . $docente;
            }
        );
    }
}
