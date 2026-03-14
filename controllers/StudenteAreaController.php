<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\AllievoAuth;
use app\models\Allievo;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Iscrizione;
use app\models\forms\AllievoLoginForm;
use app\models\forms\AllievoRegistrazioneForm;
use app\components\traits\TwoFactorActionsTrait;
use app\components\traits\ChangelogActionsTrait;

/**
 * Controller per l'area studenti autenticata.
 */
class StudenteAreaController extends Controller
{
    use TwoFactorActionsTrait;
    use ChangelogActionsTrait;
    public $layout = 'studente';

    /**
     * Prima di ogni azione, verifica che lo studente loggato abbia un profilo valido.
     * Se l'account auth non ha un allievo associato, forza il logout.
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Solo per azioni protette (non login/registrazione)
        $guestActions = ['login', 'registrazione', 'password-dimenticata', 'verifica-2fa', 'cambia-password'];
        if (!in_array($action->id, $guestActions) && !Yii::$app->studenteUser->isGuest) {
            $auth = Yii::$app->studenteUser->identity;
            if (!$auth || !$auth->allievo) {
                Yii::$app->studenteUser->logout();
                Yii::$app->session->setFlash('error', 'Profilo studente non trovato. Accedi con un codice fiscale valido.');
                return $this->redirect(['login'])->send() ? false : false;
            }
        }

        return true;
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'user' => 'studenteUser',
                'rules' => [
                    [
                        'actions' => ['login', 'registrazione', 'password-dimenticata', 'verifica-2fa', 'cambia-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'logout', 'profilo', 'bandi', 'bando', 'candidatura', 'le-mie-candidature', 'candidatura-view', 'attiva-2fa', 'disattiva-2fa', 'verifica-2fa', 'cambia-password', 'changelog', 'changelog-modal', 'segna-changelog-letto'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'forcePasswordChange' => [
                'class' => \app\components\behaviors\ForcePasswordChangeBehavior::class,
                'changePasswordAction' => 'cambia-password',
                'allowedActions' => ['login', 'logout', 'cambia-password', 'verifica-2fa', 'registrazione', 'password-dimenticata', 'error'],
                'userComponent' => 'studenteUser',
            ],
            'totpPending' => [
                'class' => \app\components\behaviors\TotpPendingBehavior::class,
                'verifyAction' => 'verifica-2fa',
                'allowedActions' => ['login', 'logout', 'verifica-2fa', 'registrazione', 'password-dimenticata', 'error'],
                'userType' => 'studente',
            ],
        ];
    }

    // =========================================================================
    // Dashboard
    // =========================================================================

    /**
     * Dashboard studente
     */
    public function actionIndex()
    {
        $allievo = $this->getStudenteLoggato();

        // Candidature totali
        $candidatureTotali = Iscrizione::find()
            ->where(['allievo_codice_fiscale' => $allievo->codice_fiscale])
            ->count();

        // Bandi aperti
        $bandiAperti = Avviso::findAperti()->count();

        // Candidature ammesse
        $candidatureAmmesse = Iscrizione::find()
            ->where(['allievo_codice_fiscale' => $allievo->codice_fiscale])
            ->andWhere(['in', 'status', [
                Iscrizione::STATUS_AMMESSO,
                Iscrizione::STATUS_AMMESSO_CON_RISERVA,
                Iscrizione::STATUS_FREQUENTANTE,
                Iscrizione::STATUS_COMPLETATO,
            ]])
            ->count();

        // Candidature in attesa
        $candidatureInAttesa = Iscrizione::find()
            ->where(['allievo_codice_fiscale' => $allievo->codice_fiscale])
            ->andWhere(['in', 'status', [
                Iscrizione::STATUS_CANDIDATO,
                Iscrizione::STATUS_IN_VALUTAZIONE,
            ]])
            ->count();

        // Bandi in scadenza (prossimi 7 giorni)
        $bandiInScadenza = Avviso::findAperti()
            ->andWhere(['<=', 'data_scadenza', date('Y-m-d', strtotime('+7 days'))])
            ->orderBy(['data_scadenza' => SORT_ASC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'allievo' => $allievo,
            'candidatureTotali' => $candidatureTotali,
            'bandiAperti' => $bandiAperti,
            'candidatureAmmesse' => $candidatureAmmesse,
            'candidatureInAttesa' => $candidatureInAttesa,
            'bandiInScadenza' => $bandiInScadenza,
        ]);
    }

    // =========================================================================
    // Autenticazione
    // =========================================================================

    /**
     * Login studente con supporto 2FA
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->studenteUser->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new AllievoLoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->validateCredentials()) {
            $auth = $model->getAuth();
            if ($auth && $auth->isTotpEnabled()) {
                // 2FA attivo: metti in stato pendente senza effettuare il login
                Yii::$app->session->set('totp_pending_user_id', $auth->id);
                Yii::$app->session->set('totp_pending_type', 'studente');
                return $this->redirect(['verifica-2fa']);
            }
            // 2FA non attivo: login diretto
            $model->login();
            Yii::$app->session->setFlash('success', 'Benvenuto nell\'area studenti!');
            return $this->redirect(['index']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Registrazione studente
     */
    public function actionRegistrazione()
    {
        $this->layout = 'login';

        if (!Yii::$app->studenteUser->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new AllievoRegistrazioneForm();

        if ($model->load(Yii::$app->request->post()) && $model->registra()) {
            Yii::$app->session->setFlash('success', 'Registrazione completata! Ora puoi accedere con le tue credenziali.');
            return $this->redirect(['login']);
        }

        return $this->render('registrazione', [
            'model' => $model,
        ]);
    }

    /**
     * Logout studente
     */
    public function actionLogout()
    {
        Yii::$app->studenteUser->logout();
        return $this->redirect(['login']);
    }

    /**
     * Password dimenticata
     */
    public function actionPasswordDimenticata()
    {
        $this->layout = 'login';

        if (Yii::$app->request->isPost) {
            $cf = Yii::$app->request->post('codice_fiscale');
            $auth = AllievoAuth::findByCodiceFiscale($cf);

            if ($auth && $auth->isActive()) {
                $auth->generatePasswordResetToken();
                if ($auth->save(false)) {
                    // TODO: Inviare email con link reset
                    Yii::$app->session->setFlash('success', 'Ti abbiamo inviato un\'email con le istruzioni per reimpostare la password.');
                }
            } else {
                // Non riveliamo se l'account esiste o meno
                Yii::$app->session->setFlash('success', 'Se l\'account esiste, riceverai un\'email con le istruzioni.');
            }

            return $this->redirect(['login']);
        }

        return $this->render('password-dimenticata');
    }

    // =========================================================================
    // Profilo
    // =========================================================================

    /**
     * Profilo studente
     */
    public function actionProfilo()
    {
        $auth = Yii::$app->studenteUser->identity;
        $allievo = $auth->allievo;

        if ($allievo->load(Yii::$app->request->post()) && $allievo->save()) {
            Yii::$app->session->setFlash('success', 'Profilo aggiornato con successo.');
            return $this->refresh();
        }

        return $this->render('profilo', [
            'allievo' => $allievo,
            'auth' => $auth,
        ]);
    }

    // =========================================================================
    // Bandi e Candidature
    // =========================================================================

    /**
     * Lista bandi aperti
     */
    public function actionBandi()
    {
        $bandi = Avviso::findAperti()
            ->orderBy(['data_scadenza' => SORT_ASC])
            ->all();

        return $this->render('bandi', [
            'bandi' => $bandi,
        ]);
    }

    /**
     * Dettaglio bando con moduli
     */
    public function actionBando($id)
    {
        $avviso = Avviso::findOne($id);

        if (!$avviso || !$avviso->isAperto()) {
            throw new NotFoundHttpException('Bando non trovato o non piu disponibile.');
        }

        $moduli = Modulo::find()
            ->where(['avviso_id' => $id])
            ->andWhere(['!=', 'status', Modulo::STATUS_ANNULLATO])
            ->orderBy(['codice' => SORT_ASC])
            ->all();

        // Verifica se lo studente ha gia una candidatura per questo bando
        $allievo = $this->getStudenteLoggato();
        $candidaturaEsistente = Iscrizione::find()
            ->where([
                'allievo_codice_fiscale' => $allievo->codice_fiscale,
                'avviso_id' => $id,
            ])
            ->andWhere(['not in', 'status', [Iscrizione::STATUS_RITIRATO, Iscrizione::STATUS_NON_AMMESSO]])
            ->one();

        return $this->render('bando', [
            'avviso' => $avviso,
            'moduli' => $moduli,
            'candidaturaEsistente' => $candidaturaEsistente,
        ]);
    }

    /**
     * Form candidatura per un bando.
     * Lo studente compila i dati formazione/lavoro, accetta privacy,
     * e il sistema crea un'Iscrizione con status='candidato'.
     */
    public function actionCandidatura($id)
    {
        $avviso = Avviso::findOne($id);

        if (!$avviso || !$avviso->isAperto()) {
            throw new NotFoundHttpException('Bando non trovato o non piu disponibile.');
        }

        $allievo = $this->getStudenteLoggato();

        // Verifica se lo studente ha gia una candidatura attiva per questo bando
        $candidaturaEsistente = Iscrizione::find()
            ->where([
                'allievo_codice_fiscale' => $allievo->codice_fiscale,
                'avviso_id' => $id,
            ])
            ->andWhere(['not in', 'status', [Iscrizione::STATUS_RITIRATO, Iscrizione::STATUS_NON_AMMESSO]])
            ->one();

        if ($candidaturaEsistente) {
            Yii::$app->session->setFlash('warning', 'Hai gia presentato una candidatura per questo bando.');
            return $this->redirect(['bando', 'id' => $id]);
        }

        $iscrizione = new Iscrizione();
        // Precompila dati dal profilo allievo
        $iscrizione->allievo_codice_fiscale = $allievo->codice_fiscale;
        $iscrizione->avviso_id = $avviso->id;
        $iscrizione->snapshotDaAllievo();

        if ($iscrizione->load(Yii::$app->request->post())) {
            $iscrizione->allievo_codice_fiscale = $allievo->codice_fiscale;
            $iscrizione->avviso_id = $avviso->id;
            $iscrizione->data_iscrizione = date('Y-m-d');
            $iscrizione->status = Iscrizione::STATUS_CANDIDATO;

            if ($iscrizione->save()) {
                // Registra nello storico
                $iscrizione->cambiaStatus(Iscrizione::STATUS_CANDIDATO, 'Candidatura presentata dallo studente');

                Yii::$app->session->setFlash('success', 'Candidatura inviata con successo! Puoi seguire lo stato dalla sezione "Le Mie Candidature".');
                return $this->redirect(['le-mie-candidature']);
            }
        }

        return $this->render('candidatura', [
            'avviso' => $avviso,
            'allievo' => $allievo,
            'iscrizione' => $iscrizione,
        ]);
    }

    /**
     * Lista candidature dello studente
     */
    public function actionLeMieCandidature()
    {
        $allievo = $this->getStudenteLoggato();

        $candidature = Iscrizione::find()
            ->where(['allievo_codice_fiscale' => $allievo->codice_fiscale])
            ->with(['avviso', 'modulo'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('le-mie-candidature', [
            'candidature' => $candidature,
        ]);
    }

    /**
     * Dettaglio candidatura con punteggi e storico
     */
    public function actionCandidaturaView($id)
    {
        $allievo = $this->getStudenteLoggato();

        $iscrizione = Iscrizione::find()
            ->where([
                'id' => $id,
                'allievo_codice_fiscale' => $allievo->codice_fiscale,
            ])
            ->with(['avviso', 'modulo', 'punteggi', 'storicoIscrizioni'])
            ->one();

        if (!$iscrizione) {
            throw new NotFoundHttpException('Candidatura non trovata.');
        }

        return $this->render('candidatura-view', [
            'iscrizione' => $iscrizione,
        ]);
    }

    // =========================================================================
    // Metodi helper per TwoFactorActionsTrait
    // =========================================================================

    protected function getTwoFactorIdentity()
    {
        return Yii::$app->studenteUser->identity;
    }

    protected function getTwoFactorUserComponent(): string
    {
        return 'studenteUser';
    }

    protected function getTwoFactorUserType(): string
    {
        return 'studente';
    }

    protected function getTwoFactorRedirectUrl(): array
    {
        return ['/studente-area/index'];
    }

    // =========================================================================
    // Metodi helper per ChangelogActionsTrait
    // =========================================================================

    protected function getChangelogUserType(): string
    {
        return 'studente';
    }

    protected function getChangelogIdentity()
    {
        return Yii::$app->studenteUser->identity;
    }

    // =========================================================================
    // Metodi helper privati
    // =========================================================================

    /**
     * Restituisce l'allievo attualmente loggato
     * @return Allievo
     */
    private function getStudenteLoggato()
    {
        $auth = Yii::$app->studenteUser->identity;
        $allievo = $auth ? $auth->allievo : null;
        if (!$allievo) {
            throw new NotFoundHttpException('Profilo studente non trovato per questo account.');
        }
        return $allievo;
    }
}
