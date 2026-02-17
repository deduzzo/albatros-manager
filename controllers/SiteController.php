<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Progetto;
use app\models\Docente;
use app\models\Allievo;
use app\models\Modulo;
use app\models\Contratto;
use app\models\Pagamento;
use app\models\FonteFinanziamento;
use app\models\AuditLog;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Calcola statistiche reali dal database

        // Conteggi base
        $progettiAttivi = Progetto::find()->where(['status' => 'attivo'])->count();
        $progettiConclusi = Progetto::find()->where(['status' => 'concluso'])->count();
        $totalProgetti = Progetto::find()->count();
        $docenti = Docente::find()->count();
        $allievi = Allievo::find()->count();

        // Budget e liquidazioni
        $budgetTotale = Progetto::find()->sum('budget_totale') ?: 0;

        // Totale liquidato (somma pagamenti con status 'pagato')
        $liquidato = Pagamento::find()
            ->where(['status' => 'pagato'])
            ->sum('importo_netto') ?: 0;

        // Da liquidare (pagamenti 'in_attesa')
        $daLiquidare = Pagamento::find()
            ->where(['status' => 'in_attesa'])
            ->sum('importo_netto') ?: 0;

        // In attesa rendiconto (pagamenti 'programmato')
        $inAttesa = Pagamento::find()
            ->where(['status' => 'programmato'])
            ->sum('importo_netto') ?: 0;

        // Ore erogate e pianificate
        $orePianificate = Modulo::find()->sum('ore_totali') ?: 0;
        // Per le ore erogate, potremmo calcolare dalle presenze o dai contratti conclusi
        // Semplifichiamo: ore dai contratti conclusi
        $oreErogate = Contratto::find()
            ->where(['status' => 'concluso'])
            ->sum('ore_contrattualizzate') ?: 0;

        // Progetti recenti (ultimi 4)
        $progettiRecenti = Progetto::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(4)
            ->all();

        // Distribuzione per fonte finanziamento
        $fonti = FonteFinanziamento::find()->all();
        $distribuzioneFonti = [];
        $etichetteFonti = [];
        $totaleBudgetFonti = 0;

        foreach ($fonti as $fonte) {
            $budgetFonte = Progetto::find()
                ->where(['fonte_finanziamento_id' => $fonte->id])
                ->sum('budget_totale') ?: 0;

            $distribuzioneFonti[] = $budgetFonte;
            $etichetteFonti[] = $fonte->descrizione;
            $totaleBudgetFonti += $budgetFonte;
        }

        // Dati per grafico liquidazioni mensili (ultimi 12 mesi)
        $liquidazioniMensili = [];
        $mesi = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];

        for ($i = 0; $i < 12; $i++) {
            $mese = date('Y-m', strtotime("-" . (11 - $i) . " months"));

            $liquidatoMese = Pagamento::find()
                ->where(['status' => 'pagato'])
                ->andWhere(['like', 'data_pagamento', $mese])
                ->sum('importo_netto') ?: 0;

            $liquidazioniMensili[] = (float)$liquidatoMese;
        }

        // Percentuali
        $percentualeLiquidato = $budgetTotale > 0 ? round(($liquidato / $budgetTotale) * 100) : 0;
        $percentualeOre = $orePianificate > 0 ? round(($oreErogate / $orePianificate) * 100) : 0;

        // Numero pratiche da liquidare
        $praticheDaLiquidare = Pagamento::find()->where(['status' => 'in_attesa'])->count();

        // Progetti da rendicontare
        $progettiDaRendicontare = Progetto::find()
            ->where(['status' => 'concluso'])
            ->count();

        // Attività recenti dall'audit log
        $attivitaRecenti = AuditLog::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(8)
            ->all();

        return $this->render('index', [
            'attivitaRecenti' => $attivitaRecenti,
            'stats' => [
                'progetti_attivi' => $progettiAttivi,
                'progetti_conclusi' => $progettiConclusi,
                'total_progetti' => $totalProgetti,
                'docenti' => $docenti,
                'allievi' => $allievi,
                'budget_totale' => $budgetTotale,
                'liquidato' => $liquidato,
                'da_liquidare' => $daLiquidare,
                'in_attesa' => $inAttesa,
                'ore_erogate' => $oreErogate,
                'ore_pianificate' => $orePianificate,
                'percentuale_liquidato' => $percentualeLiquidato,
                'percentuale_ore' => $percentualeOre,
                'pratiche_da_liquidare' => $praticheDaLiquidare,
                'progetti_da_rendicontare' => $progettiDaRendicontare,
            ],
            'progettiRecenti' => $progettiRecenti,
            'distribuzioneFonti' => $distribuzioneFonti,
            'etichetteFonti' => $etichetteFonti,
            'totaleBudgetFonti' => $totaleBudgetFonti,
            'liquidazioniMensili' => $liquidazioniMensili,
            'mesi' => $mesi,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
