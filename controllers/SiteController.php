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
use app\models\Avviso;
use app\models\forms\DocenteLoginForm;
use app\models\forms\AllievoLoginForm;

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
                        'actions' => ['login', 'error', 'manuale-admin', 'manuale-docente', 'manuale-studente', 'guida-admin', 'guida-docente', 'supporto'],
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
     * Login unificato: gestisce admin, docente e mostra contenuti pubblici.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        // Se admin gia loggato, vai alla dashboard admin
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // Se docente gia loggato, vai all'area docenti
        if (!Yii::$app->docenteUser->isGuest) {
            return $this->redirect(['/docente-area/index']);
        }

        // Se studente gia loggato, vai all'area studenti
        if (!Yii::$app->studenteUser->isGuest) {
            return $this->redirect(['/studente-area/index']);
        }

        $loginForm = new LoginForm();
        $docenteLoginForm = new DocenteLoginForm();
        $studenteLoginForm = new AllievoLoginForm();
        $loginError = null;
        $docenteError = null;
        $activeCard = null;

        // Gestione POST in base al tipo di login
        if (Yii::$app->request->isPost) {
            $loginType = Yii::$app->request->post('login_type', 'admin');

            if ($loginType === 'docente') {
                $activeCard = 'docente';
                if ($docenteLoginForm->load(Yii::$app->request->post()) && $docenteLoginForm->login()) {
                    Yii::$app->session->setFlash('success', 'Benvenuto nell\'area docenti!');
                    return $this->redirect(['/docente-area/index']);
                }
                $docenteError = true;
            } elseif ($loginType === 'studente') {
                $activeCard = 'studente';
                if ($studenteLoginForm->load(Yii::$app->request->post()) && $studenteLoginForm->login()) {
                    Yii::$app->session->setFlash('success', 'Benvenuto nell\'area studenti!');
                    return $this->redirect(['/studente-area/index']);
                }
            } else {
                $activeCard = 'admin';
                if ($loginForm->load(Yii::$app->request->post()) && $loginForm->login()) {
                    return $this->goBack();
                }
                $loginForm->password = '';
                $loginError = true;
            }
        }

        // Dati pubblici: bandi aperti
        $bandiAperti = Avviso::findAperti()
            ->with(['progetto', 'moduli'])
            ->orderBy(['data_scadenza' => SORT_ASC])
            ->limit(6)
            ->all();

        // CF demo per la card credenziali (solo in demoMode)
        $demoDocenti = [];
        $demoAllievi = [];
        if (Yii::$app->params['demoMode'] ?? false) {
            $demoDocenti = Docente::find()
                ->select(['codice_fiscale', 'cognome', 'nome'])
                ->where(['deleted_at' => null])
                ->orderBy(['cognome' => SORT_ASC])
                ->limit(3)
                ->asArray()
                ->all();
            $demoAllievi = Allievo::find()
                ->select(['codice_fiscale', 'cognome', 'nome'])
                ->where(['deleted_at' => null])
                ->orderBy(['cognome' => SORT_ASC])
                ->limit(3)
                ->asArray()
                ->all();
        }

        return $this->render('login', [
            'loginForm' => $loginForm,
            'docenteLoginForm' => $docenteLoginForm,
            'studenteLoginForm' => $studenteLoginForm,
            'bandiAperti' => $bandiAperti,
            'loginError' => $loginError,
            'docenteError' => $docenteError,
            'activeCard' => $activeCard,
            'demoDocenti' => $demoDocenti,
            'demoAllievi' => $demoAllievi,
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
     * Pagina profilo utente.
     */
    public function actionProfilo()
    {
        $user = Yii::$app->user->identity;
        $model = new \app\models\forms\ProfiloForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->salva()) {
            Yii::$app->session->setFlash('success', 'Profilo aggiornato con successo.');
            return $this->redirect(['profilo']);
        }

        return $this->render('profilo', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * Pagina impostazioni (cambio password).
     */
    public function actionImpostazioni()
    {
        $user = Yii::$app->user->identity;
        $model = new \app\models\forms\CambioPasswordForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->salva()) {
            Yii::$app->session->setFlash('success', 'Password modificata con successo.');
            return $this->redirect(['impostazioni']);
        }

        return $this->render('impostazioni', [
            'model' => $model,
            'user' => $user,
        ]);
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

    /**
     * Manuale Amministratore (accessibile senza login)
     */
    public function actionManualeAdmin()
    {
        $this->layout = 'login';
        return $this->render('manuale-admin');
    }

    /**
     * Manuale Docente (accessibile senza login)
     */
    public function actionManualeDocente()
    {
        $this->layout = 'login';
        return $this->render('manuale-docente');
    }

    /**
     * Manuale Studente (accessibile senza login)
     */
    public function actionManualeStudente()
    {
        $this->layout = 'login';
        return $this->render('manuale-studente');
    }

    /**
     * Guida interattiva Amministratore (usa layout admin per autenticati, login per guest)
     */
    public function actionGuidaAdmin()
    {
        if (Yii::$app->user->isGuest) {
            $this->layout = 'login';
        }
        return $this->render('guida-admin');
    }

    /**
     * Guida interattiva Docente
     */
    public function actionGuidaDocente()
    {
        $this->layout = 'login';
        return $this->render('guida-docente');
    }

    /**
     * Form segnalazione problemi (accessibile a tutti, layout pulito)
     */
    public function actionSupporto()
    {
        $this->layout = 'login';

        if (Yii::$app->request->isPost) {
            $data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'nome' => Yii::$app->request->post('nome', ''),
                'email' => Yii::$app->request->post('email', ''),
                'tipo' => Yii::$app->request->post('tipo', 'altro'),
                'descrizione' => Yii::$app->request->post('descrizione', ''),
                'user_agent' => Yii::$app->request->userAgent,
                'ip' => Yii::$app->request->userIP,
                'url_referrer' => Yii::$app->request->referrer,
                'admin_user_id' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
                'docente_cf' => Yii::$app->docenteUser->isGuest ? null : (Yii::$app->docenteUser->identity->docente_codice_fiscale ?? null),
            ];

            // Salva in file JSON nella directory runtime/support
            $dir = Yii::getAlias('@runtime/support');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $filename = 'segnalazione_' . date('Ymd_His') . '_' . Yii::$app->security->generateRandomString(6) . '.json';
            file_put_contents($dir . '/' . $filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Gestione upload screenshot
            $screenshot = \yii\web\UploadedFile::getInstanceByName('screenshot');
            if ($screenshot && $screenshot->size <= 5 * 1024 * 1024 && in_array($screenshot->extension, ['jpg', 'jpeg', 'png'])) {
                $screenshotName = pathinfo($filename, PATHINFO_FILENAME) . '.' . $screenshot->extension;
                $screenshot->saveAs($dir . '/' . $screenshotName);
            }

            return $this->render('supporto-grazie');
        }

        // Precompila dati se utente autenticato
        $nome = '';
        $email = '';
        if (!Yii::$app->user->isGuest) {
            $nome = Yii::$app->user->identity->username ?? '';
            $email = Yii::$app->user->identity->email ?? '';
        } elseif (!Yii::$app->docenteUser->isGuest) {
            $docente = Yii::$app->docenteUser->identity->docente ?? null;
            if ($docente) {
                $nome = $docente->cognome . ' ' . $docente->nome;
                $email = $docente->email ?? '';
            }
        }

        return $this->render('supporto', [
            'nome' => $nome,
            'email' => $email,
        ]);
    }
}
