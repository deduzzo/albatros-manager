<?php

namespace app\controllers;

use Yii;
use app\models\RendicontazioneOre;
use app\models\RendicontazioneOreSearch;
use app\models\Assegnazione;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * RendicontazioneOreController - gestione ore rendicontate dai docenti.
 */
class RendicontazioneOreController extends Controller
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
                'actions' => [
                    'delete' => ['POST'],
                    'approva' => ['POST'],
                    'rifiuta' => ['POST'],
                    'approva-multi' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista ore rendicontate per un'assegnazione.
     * @param int $assegnazione_id
     * @return string
     */
    public function actionIndex($assegnazione_id)
    {
        $assegnazione = $this->findAssegnazione($assegnazione_id);
        $searchModel = new RendicontazioneOreSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $assegnazione_id);

        $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione_id);
        $oreInserite = RendicontazioneOre::getTotaleOreInserite($assegnazione_id);
        $oreContrattualizzate = $assegnazione->contratto ? $assegnazione->contratto->ore_contrattualizzate : 0;

        return $this->render('index', [
            'assegnazione' => $assegnazione,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'oreApprovate' => $oreApprovate,
            'oreInserite' => $oreInserite,
            'oreContrattualizzate' => $oreContrattualizzate,
        ]);
    }

    /**
     * Visualizza il dettaglio di una singola rendicontazione ore.
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea una nuova rendicontazione ore.
     * @param int $assegnazione_id
     * @return string|\yii\web\Response
     */
    public function actionCreate($assegnazione_id)
    {
        $assegnazione = $this->findAssegnazione($assegnazione_id);
        $model = new RendicontazioneOre();
        $model->assegnazione_id = $assegnazione_id;
        $model->status = RendicontazioneOre::STATUS_INSERITO;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rendicontazione ore inserita con successo.');
            return $this->redirect(['index', 'assegnazione_id' => $assegnazione_id]);
        }

        return $this->render('create', [
            'model' => $model,
            'assegnazione' => $assegnazione,
        ]);
    }

    /**
     * Modifica una rendicontazione ore (solo se status = inserito).
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            Yii::$app->session->setFlash('error', 'Non e\' possibile modificare una rendicontazione gia\' approvata o rifiutata.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rendicontazione ore aggiornata con successo.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        return $this->render('update', [
            'model' => $model,
            'assegnazione' => $model->assegnazione,
        ]);
    }

    /**
     * Elimina una rendicontazione (solo se status = inserito).
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            Yii::$app->session->setFlash('error', 'Non e\' possibile eliminare una rendicontazione gia\' approvata o rifiutata.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        $assegnazioneId = $model->assegnazione_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Rendicontazione ore eliminata con successo.');

        return $this->redirect(['index', 'assegnazione_id' => $assegnazioneId]);
    }

    /**
     * Approva una singola rendicontazione ore.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionApprova($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            Yii::$app->session->setFlash('warning', 'Questa rendicontazione non puo\' essere approvata.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        $model->status = RendicontazioneOre::STATUS_APPROVATO;
        $model->approvato_da = Yii::$app->user->id;
        $model->approvato_at = date('Y-m-d H:i:s');

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Rendicontazione del ' . Yii::$app->formatter->asDate($model->data) . ' approvata.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante l\'approvazione.');
        }

        return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
    }

    /**
     * Rifiuta una singola rendicontazione ore.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRifiuta($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            Yii::$app->session->setFlash('warning', 'Questa rendicontazione non puo\' essere rifiutata.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        $motivoRifiuto = Yii::$app->request->post('motivo_rifiuto');
        if (empty($motivoRifiuto)) {
            Yii::$app->session->setFlash('error', 'Il motivo del rifiuto e\' obbligatorio.');
            return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
        }

        $model->status = RendicontazioneOre::STATUS_RIFIUTATO;
        $model->motivo_rifiuto = $motivoRifiuto;
        $model->approvato_da = Yii::$app->user->id;
        $model->approvato_at = date('Y-m-d H:i:s');

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Rendicontazione del ' . Yii::$app->formatter->asDate($model->data) . ' rifiutata.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante il rifiuto.');
        }

        return $this->redirect(['index', 'assegnazione_id' => $model->assegnazione_id]);
    }

    /**
     * Approva tutte le rendicontazioni in attesa per una assegnazione (bulk approve).
     * @param int $assegnazione_id
     * @return \yii\web\Response
     */
    public function actionApprovaMulti($assegnazione_id)
    {
        $assegnazione = $this->findAssegnazione($assegnazione_id);

        $rendicontazioni = RendicontazioneOre::find()
            ->where([
                'assegnazione_id' => $assegnazione_id,
                'status' => RendicontazioneOre::STATUS_INSERITO,
            ])
            ->all();

        $count = 0;
        foreach ($rendicontazioni as $model) {
            $model->status = RendicontazioneOre::STATUS_APPROVATO;
            $model->approvato_da = Yii::$app->user->id;
            $model->approvato_at = date('Y-m-d H:i:s');
            if ($model->save(false)) {
                $count++;
            }
        }

        if ($count > 0) {
            Yii::$app->session->setFlash('success', $count . ' rendicontazioni approvate con successo.');
        } else {
            Yii::$app->session->setFlash('info', 'Nessuna rendicontazione da approvare.');
        }

        return $this->redirect(['index', 'assegnazione_id' => $assegnazione_id]);
    }

    /**
     * Dashboard admin: panoramica di tutte le assegnazioni con ore pendenti.
     * @return string
     */
    public function actionDashboard()
    {
        // Assegnazioni che hanno almeno una rendicontazione ore con status='inserito'
        $assegnazioniConPendenti = Assegnazione::findWithDeleted()
            ->alias('a')
            ->leftJoin(
                '{{%rendicontazione_ore}} ro',
                'ro.assegnazione_id = a.id AND ro.deleted_at IS NULL AND ro.status = :statusIns',
                [':statusIns' => RendicontazioneOre::STATUS_INSERITO]
            )
            ->andWhere(['a.deleted_at' => null])
            ->andWhere(['IS NOT', 'ro.id', null])
            ->groupBy('a.id')
            ->all();

        $righe = [];
        $totaleOreDaApprovare = 0;
        $totaleOreApprovateGlobale = 0;

        foreach ($assegnazioniConPendenti as $assegnazione) {
            $docente = $assegnazione->docente;
            $modulo = $assegnazione->modulo;
            $avviso = $modulo ? $modulo->avviso : null;
            $oreContratto = $assegnazione->contratto ? $assegnazione->contratto->ore_contrattualizzate : 0;
            $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
            $oreInAttesa = RendicontazioneOre::getTotaleOreInserite($assegnazione->id);

            $totaleOreDaApprovare += $oreInAttesa;
            $totaleOreApprovateGlobale += $oreApprovate;

            $righe[] = [
                'assegnazione' => $assegnazione,
                'docente' => $docente,
                'modulo' => $modulo,
                'avviso' => $avviso,
                'oreContratto' => $oreContratto,
                'oreApprovate' => $oreApprovate,
                'oreInAttesa' => $oreInAttesa,
            ];
        }

        // Ordina per ore in attesa DESC
        usort($righe, function ($a, $b) {
            return $b['oreInAttesa'] <=> $a['oreInAttesa'];
        });

        // Statistiche totali ore approvate (anche per assegnazioni senza pendenti)
        $totaleOreApprovateTutte = (float) RendicontazioneOre::findWithDeleted()
            ->alias('ro')
            ->leftJoin('{{%assegnazione}} a', 'a.id = ro.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['ro.status' => RendicontazioneOre::STATUS_APPROVATO])
            ->andWhere(['ro.deleted_at' => null])
            ->sum('ro.ore') ?: 0;

        return $this->render('dashboard', [
            'righe' => $righe,
            'totaleAssegnazioniPendenti' => count($assegnazioniConPendenti),
            'totaleOreDaApprovare' => $totaleOreDaApprovare,
            'totaleOreApprovate' => $totaleOreApprovateTutte,
        ]);
    }

    /**
     * Riepilogo mensile delle ore per un'assegnazione.
     * @param int $assegnazione_id
     * @return string
     */
    public function actionRiepilogo($assegnazione_id)
    {
        $assegnazione = $this->findAssegnazione($assegnazione_id);

        $oreContrattualizzate = $assegnazione->contratto ? $assegnazione->contratto->ore_contrattualizzate : 0;
        $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione_id);
        $oreInserite = RendicontazioneOre::getTotaleOreInserite($assegnazione_id);

        // Riepilogo mensile
        $riepilogoMensile = RendicontazioneOre::find()
            ->select([
                'DATE_FORMAT(data, "%Y-%m") as mese',
                'SUM(CASE WHEN status = "approvato" THEN ore ELSE 0 END) as ore_approvate',
                'SUM(CASE WHEN status = "inserito" THEN ore ELSE 0 END) as ore_inserite',
                'SUM(CASE WHEN status = "rifiutato" THEN ore ELSE 0 END) as ore_rifiutate',
                'COUNT(*) as num_giornate',
            ])
            ->where(['assegnazione_id' => $assegnazione_id])
            ->groupBy(['DATE_FORMAT(data, "%Y-%m")'])
            ->orderBy(['mese' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->render('riepilogo', [
            'assegnazione' => $assegnazione,
            'oreContrattualizzate' => $oreContrattualizzate,
            'oreApprovate' => $oreApprovate,
            'oreInserite' => $oreInserite,
            'riepilogoMensile' => $riepilogoMensile,
        ]);
    }

    /**
     * Trova il model RendicontazioneOre.
     * @param int $id
     * @return RendicontazioneOre
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = RendicontazioneOre::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La rendicontazione richiesta non esiste.');
    }

    /**
     * Trova il model Assegnazione.
     * @param int $id
     * @return Assegnazione
     * @throws NotFoundHttpException
     */
    protected function findAssegnazione($id)
    {
        if (($model = Assegnazione::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('L\'assegnazione richiesta non esiste.');
    }
}
