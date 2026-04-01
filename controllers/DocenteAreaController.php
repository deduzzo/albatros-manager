<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\DocenteAuth;
use app\models\Docente;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Istanza;
use app\models\Assegnazione;
use app\models\Contratto;
use app\models\RendicontazioneOre;
use app\models\IstanzaTag;
use app\models\IstanzaDocumento;
use app\models\IstanzaSnapshot;
use app\models\TagCurriculum;
use app\models\DocenteTag;
use app\models\RimborsoSpesa;
use app\models\RichiestaFatturazione;
use app\models\Calendario;
use app\models\PresenzaAllievo;
use app\models\Iscrizione;
use app\models\forms\DocenteLoginForm;
use app\models\forms\DocenteRegistrazioneForm;
use app\models\forms\IscrizioneForm;
use app\components\traits\TwoFactorActionsTrait;
use app\components\traits\ChangelogActionsTrait;
use app\components\traits\NotificheActionsTrait;
use yii\web\UploadedFile;

/**
 * Controller per l'area docenti.
 */
class DocenteAreaController extends Controller
{
    use TwoFactorActionsTrait;
    use ChangelogActionsTrait;
    use NotificheActionsTrait;
    public $layout = 'docente';

    /**
     * Prima di ogni azione, verifica che il docente loggato abbia un profilo valido.
     * Se l'account auth non ha un docente associato, forza il logout.
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Solo per azioni protette (non login/registrazione/2fa)
        $guestActions = ['login', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password', 'verifica-2fa', 'cambia-password'];
        if (!in_array($action->id, $guestActions) && !Yii::$app->docenteUser->isGuest) {
            $auth = Yii::$app->docenteUser->identity;
            if (!$auth || !$auth->docente) {
                Yii::$app->docenteUser->logout();
                Yii::$app->session->setFlash('error', 'Profilo docente non trovato. Accedi con un codice fiscale valido.');
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
                'user' => 'docenteUser',
                'rules' => [
                    [
                        'actions' => ['login', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password', 'verifica-2fa', 'cambia-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'logout', 'profilo', 'bandi', 'bando', 'iscrizione', 'le-mie-iscrizioni', 'iscrizione-view', 'i-miei-contratti', 'contratto-view', 'le-mie-assegnazioni', 'inserisci-ore', 'le-mie-ore', 'modifica-ore', 'elimina-ore', 'guida', 'rimborsi', 'nuovo-rimborso', 'modifica-rimborso', 'contratti', 'contratto-download', 'documenti', 'attiva-2fa', 'disattiva-2fa', 'verifica-2fa', 'cambia-password', 'changelog', 'changelog-modal', 'segna-changelog-letto', 'notifiche', 'notifica-view', 'segna-letta', 'segna-tutte-lette', 'presenze', 'presenze-lezione', 'salva-presenze'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'elimina-ore' => ['POST'],
                'salva-presenze' => ['POST'],
                ],
            ],
            'forcePasswordChange' => [
                'class' => \app\components\behaviors\ForcePasswordChangeBehavior::class,
                'changePasswordAction' => 'cambia-password',
                'allowedActions' => ['login', 'logout', 'cambia-password', 'verifica-2fa', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password', 'error'],
                'userComponent' => 'docenteUser',
            ],
            'totpPending' => [
                'class' => \app\components\behaviors\TotpPendingBehavior::class,
                'verifyAction' => 'verifica-2fa',
                'allowedActions' => ['login', 'logout', 'verifica-2fa', 'cambia-password', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password', 'error'],
                'userType' => 'docente',
            ],
        ];
    }

    /**
     * Dashboard docente
     */
    public function actionIndex()
    {
        $auth = Yii::$app->docenteUser->identity;
        $docente = $auth->docente;

        // Conta iscrizioni
        $istanzeCount = Istanza::find()
            ->where(['docente_codice_fiscale' => $docente->codice_fiscale])
            ->count();

        // Bandi aperti
        $bandiAperti = Avviso::findAperti()->count();

        // Moduli aperti per iscrizione
        $moduliAperti = Modulo::findApertiPerIscrizioni()->count();

        // Contratti attivi
        $contrattiAttivi = Contratto::find()
            ->innerJoin('assegnazione a', 'a.id = contratto.assegnazione_id')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['contratto.status' => Contratto::STATUS_ATTIVO])
            ->all();

        // Contratti in scadenza (lezioni che terminano entro 14 giorni)
        $contrattiInScadenza = [];
        foreach ($contrattiAttivi as $contratto) {
            if ($contratto->isLezioniInScadenza(14)) {
                $contrattiInScadenza[] = $contratto;
            }
        }

        // Assegnazioni attive del docente
        $assegnazioniAttive = Assegnazione::find()
            ->innerJoin('contratto c', 'c.assegnazione_id = assegnazione.id AND c.deleted_at IS NULL')
            ->where(['assegnazione.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['c.status' => [Contratto::STATUS_ATTIVO, Contratto::STATUS_FIRMATO]])
            ->andWhere(['assegnazione.deleted_at' => null])
            ->count();

        // Ore totali approvate del docente
        $oreTotaliApprovate = (float) RendicontazioneOre::find()
            ->innerJoin('assegnazione a', 'a.id = rendicontazione_ore.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['rendicontazione_ore.status' => RendicontazioneOre::STATUS_APPROVATO])
            ->andWhere(['rendicontazione_ore.deleted_at' => null])
            ->sum('rendicontazione_ore.ore') ?: 0;

        // Ore in attesa di validazione
        $oreInAttesa = (float) RendicontazioneOre::find()
            ->innerJoin('assegnazione a', 'a.id = rendicontazione_ore.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['rendicontazione_ore.status' => RendicontazioneOre::STATUS_INSERITO])
            ->andWhere(['rendicontazione_ore.deleted_at' => null])
            ->sum('rendicontazione_ore.ore') ?: 0;

        return $this->render('index', [
            'docente' => $docente,
            'istanzeCount' => $istanzeCount,
            'bandiAperti' => $bandiAperti,
            'moduliAperti' => $moduliAperti,
            'assegnazioniAttive' => $assegnazioniAttive,
            'oreTotaliApprovate' => $oreTotaliApprovate,
            'oreInAttesa' => $oreInAttesa,
        ]);
    }

    /**
     * Login docente con supporto 2FA
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->docenteUser->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new DocenteLoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->validateCredentials()) {
            $auth = $model->getAuth();
            if ($auth && $auth->isTotpEnabled()) {
                // 2FA attivo: metti in stato pendente senza effettuare il login
                Yii::$app->session->set('totp_pending_user_id', $auth->id);
                Yii::$app->session->set('totp_pending_type', 'docente');
                return $this->redirect(['verifica-2fa']);
            }
            // 2FA non attivo: login diretto
            $model->login();
            Yii::$app->session->setFlash('success', 'Benvenuto nell\'area docenti!');
            return $this->redirect(['index']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Registrazione docente
     */
    public function actionRegistrazione()
    {
        $this->layout = 'login';

        if (!Yii::$app->docenteUser->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new DocenteRegistrazioneForm();

        if ($model->load(Yii::$app->request->post()) && $model->registra()) {
            Yii::$app->session->setFlash('success', 'Registrazione completata! Ti abbiamo inviato un\'email di verifica.');
            return $this->redirect(['login']);
        }

        return $this->render('registrazione', [
            'model' => $model,
        ]);
    }

    /**
     * Verifica email
     */
    public function actionVerificaEmail($token)
    {
        $this->layout = 'login';

        $auth = DocenteAuth::findByEmailVerifyToken($token);

        if (!$auth) {
            Yii::$app->session->setFlash('error', 'Token di verifica non valido o scaduto.');
            return $this->redirect(['login']);
        }

        if ($auth->confirmEmail()) {
            Yii::$app->session->setFlash('success', 'Email verificata con successo! Ora puoi accedere.');
        } else {
            Yii::$app->session->setFlash('error', 'Errore durante la verifica dell\'email.');
        }

        return $this->redirect(['login']);
    }

    /**
     * Logout docente
     */
    public function actionLogout()
    {
        Yii::$app->docenteUser->logout();
        return $this->redirect(['login']);
    }

    /**
     * Guida interattiva area docenti
     */
    public function actionGuida()
    {
        return $this->render('guida');
    }

    /**
     * Profilo docente
     */
    public function actionProfilo()
    {
        $auth = Yii::$app->docenteUser->identity;
        $docente = $auth->docente;

        if ($docente->load(Yii::$app->request->post()) && $docente->save()) {
            Yii::$app->session->setFlash('success', 'Profilo aggiornato con successo.');
            return $this->refresh();
        }

        return $this->render('profilo', [
            'docente' => $docente,
            'auth' => $auth,
        ]);
    }

    /**
     * Lista bandi aperti
     */
    public function actionBandi()
    {
        $bandi = Avviso::findAperti()->all();

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
            throw new NotFoundHttpException('Bando non trovato o non più disponibile.');
        }

        $moduli = Modulo::find()
            ->where(['avviso_id' => $id, 'status' => Modulo::STATUS_APERTO])
            ->all();

        // Verifica iscrizioni già effettuate
        $auth = Yii::$app->docenteUser->identity;
        $iscrizioniEffettuate = Istanza::find()
            ->where(['docente_codice_fiscale' => $auth->docente_codice_fiscale, 'avviso_id' => $id])
            ->indexBy('modulo_id')
            ->all();

        return $this->render('bando', [
            'avviso' => $avviso,
            'moduli' => $moduli,
            'iscrizioniEffettuate' => $iscrizioniEffettuate,
        ]);
    }

    /**
     * Wizard iscrizione a modulo
     */
    public function actionIscrizione($moduloId)
    {
        $modulo = Modulo::findOne($moduloId);

        if (!$modulo || !$modulo->isApertoIscrizioni()) {
            throw new NotFoundHttpException('Modulo non disponibile per iscrizioni.');
        }

        $auth = Yii::$app->docenteUser->identity;
        $docente = $auth->docente;

        // Verifica se già iscritto
        $esistente = Istanza::find()
            ->where([
                'docente_codice_fiscale' => $docente->codice_fiscale,
                'modulo_id' => $moduloId,
            ])
            ->one();

        if ($esistente) {
            Yii::$app->session->setFlash('warning', 'Hai già presentato un\'iscrizione per questo modulo.');
            return $this->redirect(['bando', 'id' => $modulo->avviso_id]);
        }

        $model = new IscrizioneForm();
        $model->moduloId = $moduloId;
        $model->docenteCodiceFiscale = $docente->codice_fiscale;

        // Tag disponibili
        $tagCategorie = \app\models\TagCategoria::find()->with('tags')->all();

        // Tag già presenti nel profilo
        $tagsDocente = DocenteTag::find()
            ->where(['docente_codice_fiscale' => $docente->codice_fiscale])
            ->indexBy('tag_id')
            ->all();

        // Documenti richiesti per il bando
        $documentiRichiesti = $modulo->avviso->documentiRichiesti;

        if ($model->load(Yii::$app->request->post())) {
            $model->documenti = UploadedFile::getInstances($model, 'documenti');

            if ($model->validate() && $model->salva()) {
                Yii::$app->session->setFlash('success', 'Iscrizione inviata con successo!');
                return $this->redirect(['le-mie-iscrizioni']);
            }
        }

        return $this->render('iscrizione', [
            'model' => $model,
            'modulo' => $modulo,
            'docente' => $docente,
            'tagCategorie' => $tagCategorie,
            'tagsDocente' => $tagsDocente,
            'documentiRichiesti' => $documentiRichiesti,
        ]);
    }

    /**
     * Le mie iscrizioni
     */
    public function actionLeMieIscrizioni()
    {
        $auth = Yii::$app->docenteUser->identity;

        $iscrizioni = Istanza::find()
            ->where(['docente_codice_fiscale' => $auth->docente_codice_fiscale])
            ->with(['avviso', 'modulo'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('le-mie-iscrizioni', [
            'iscrizioni' => $iscrizioni,
        ]);
    }

    /**
     * Password dimenticata
     */
    public function actionPasswordDimenticata()
    {
        $this->layout = 'login';

        if (Yii::$app->request->isPost) {
            $cf = Yii::$app->request->post('codice_fiscale');
            $auth = DocenteAuth::findByCodiceFiscale($cf);

            if ($auth && $auth->isActive()) {
                $auth->generatePasswordResetToken();
                if ($auth->save(false)) {
                    // Invio email con link reset
                    $docente = $auth->docente;
                    $email = $docente ? $docente->email : null;

                    if ($email) {
                        $resetLink = Yii::$app->urlManager->createAbsoluteUrl([
                            'area-docenti/reset-password',
                            'token' => $auth->password_reset_token,
                        ]);

                        try {
                            Yii::$app->mailer->compose(
                                ['html' => 'password-reset-html', 'text' => 'password-reset-text'],
                                [
                                    'nome' => $docente->getNomeCompleto(),
                                    'resetLink' => $resetLink,
                                    'expiry' => '24 ore',
                                ]
                            )
                                ->setTo($email)
                                ->setSubject('Reimposta la tua password - ' . (\app\models\Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros'))
                                ->send();
                        } catch (\Throwable $e) {
                            Yii::error('Errore invio email reset password docente: ' . $e->getMessage(), __METHOD__);
                        }
                    }

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

    /**
     * Reset password
     */
    public function actionResetPassword($token)
    {
        $this->layout = 'login';

        $auth = DocenteAuth::findByPasswordResetToken($token);

        if (!$auth) {
            Yii::$app->session->setFlash('error', 'Token non valido o scaduto.');
            return $this->redirect(['login']);
        }

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('password');
            $passwordConfirm = Yii::$app->request->post('password_confirm');

            if (strlen($password) < 6) {
                Yii::$app->session->setFlash('error', 'La password deve essere di almeno 6 caratteri.');
            } elseif ($password !== $passwordConfirm) {
                Yii::$app->session->setFlash('error', 'Le password non coincidono.');
            } else {
                $auth->setPassword($password);
                $auth->removePasswordResetToken();
                if ($auth->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reimpostata con successo!');
                    return $this->redirect(['login']);
                }
            }
        }

        return $this->render('reset-password', [
            'token' => $token,
        ]);
    }

    // =========================================================================
    // Rendicontazione Ore
    // =========================================================================

    /**
     * Lista assegnazioni del docente con stato ore
     */
    public function actionLeMieAssegnazioni()
    {
        $docente = $this->getDocenteLoggato();

        $assegnazioni = Assegnazione::find()
            ->where(['docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['assegnazione.deleted_at' => null])
            ->with(['modulo', 'modulo.avviso', 'contratto'])
            ->orderBy(['data_assegnazione' => SORT_DESC])
            ->all();

        // Calcola totali per le stat cards
        $totaleOreContrattualizzate = 0;
        $totaleOreApprovate = 0;
        $totaleOreDaValidare = 0;
        foreach ($assegnazioni as $assegnazione) {
            $totaleOreContrattualizzate += $assegnazione->getOreContrattualizzate() ?: 0;
            $totaleOreApprovate += RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
            $totaleOreDaValidare += RendicontazioneOre::getTotaleOreInserite($assegnazione->id);
        }

        return $this->render('le-mie-assegnazioni', [
            'assegnazioni' => $assegnazioni,
            'totaleAssegnazioni' => count($assegnazioni),
            'totaleOreContrattualizzate' => $totaleOreContrattualizzate,
            'totaleOreApprovate' => $totaleOreApprovate,
            'totaleOreDaValidare' => $totaleOreDaValidare,
        ]);
    }

    /**
     * Inserimento ore giornaliere per un'assegnazione
     */
    public function actionInserisciOre($assegnazione_id)
    {
        $docente = $this->getDocenteLoggato();
        $assegnazione = $this->findAssegnazioneDocente($assegnazione_id, $docente);

        $model = new RendicontazioneOre();
        $model->assegnazione_id = $assegnazione->id;
        $model->status = RendicontazioneOre::STATUS_INSERITO;
        $model->firma_digitale = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ore inserite con successo. In attesa di validazione.');
            return $this->redirect(['le-mie-ore', 'assegnazione_id' => $assegnazione->id]);
        }

        // Dati per la vista
        $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
        $oreInAttesa = RendicontazioneOre::getTotaleOreInserite($assegnazione->id);

        return $this->render('inserisci-ore', [
            'model' => $model,
            'assegnazione' => $assegnazione,
            'oreApprovate' => $oreApprovate,
            'oreInAttesa' => $oreInAttesa,
            'isModifica' => false,
        ]);
    }

    /**
     * Lista ore inserite per un'assegnazione
     */
    public function actionLeMieOre($assegnazione_id)
    {
        $docente = $this->getDocenteLoggato();
        $assegnazione = $this->findAssegnazioneDocente($assegnazione_id, $docente);

        $rendicontazioni = RendicontazioneOre::find()
            ->where(['assegnazione_id' => $assegnazione->id])
            ->orderBy(['data' => SORT_DESC])
            ->all();

        $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
        $oreInAttesa = RendicontazioneOre::getTotaleOreInserite($assegnazione->id);
        $oreRifiutate = (float) RendicontazioneOre::find()
            ->where([
                'assegnazione_id' => $assegnazione->id,
                'status' => RendicontazioneOre::STATUS_RIFIUTATO,
            ])
            ->sum('ore') ?: 0;

        return $this->render('le-mie-ore', [
            'rendicontazioni' => $rendicontazioni,
            'assegnazione' => $assegnazione,
            'oreApprovate' => $oreApprovate,
            'oreInAttesa' => $oreInAttesa,
            'oreRifiutate' => $oreRifiutate,
        ]);
    }

    /**
     * Modifica ore (solo se status=inserito)
     */
    public function actionModificaOre($id)
    {
        $docente = $this->getDocenteLoggato();
        $model = $this->findRendicontazioneOreDocente($id, $docente);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            throw new ForbiddenHttpException('Non puoi modificare ore gia\' approvate o rifiutate.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ore modificate con successo.');
            return $this->redirect(['le-mie-ore', 'assegnazione_id' => $model->assegnazione_id]);
        }

        $assegnazione = $model->assegnazione;
        $oreApprovate = RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
        $oreInAttesa = RendicontazioneOre::getTotaleOreInserite($assegnazione->id);

        return $this->render('modifica-ore', [
            'model' => $model,
            'assegnazione' => $assegnazione,
            'oreApprovate' => $oreApprovate,
            'oreInAttesa' => $oreInAttesa,
            'isModifica' => true,
        ]);
    }

    /**
     * Elimina ore (solo se status=inserito, solo POST)
     */
    public function actionEliminaOre($id)
    {
        $docente = $this->getDocenteLoggato();
        $model = $this->findRendicontazioneOreDocente($id, $docente);

        if ($model->status !== RendicontazioneOre::STATUS_INSERITO) {
            throw new ForbiddenHttpException('Non puoi eliminare ore gia\' approvate o rifiutate.');
        }

        $assegnazioneId = $model->assegnazione_id;
        $model->delete();

        Yii::$app->session->setFlash('success', 'Registrazione ore eliminata.');
        return $this->redirect(['le-mie-ore', 'assegnazione_id' => $assegnazioneId]);
    }

    // =========================================================================
    // Rimborsi Spesa
    // =========================================================================

    /**
     * Lista rimborsi spesa del docente
     */
    public function actionRimborsi()
    {
        $docente = $this->getDocenteLoggato();

        $rimborsi = RimborsoSpesa::find()
            ->innerJoin('assegnazione a', 'a.id = rimborso_spesa.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['rimborso_spesa.deleted_at' => null])
            ->with(['assegnazione.modulo'])
            ->orderBy(['rimborso_spesa.created_at' => SORT_DESC])
            ->all();

        // Assegnazioni attive del docente (per il dropdown "nuovo rimborso")
        $assegnazioni = Assegnazione::find()
            ->where(['docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['assegnazione.deleted_at' => null])
            ->with(['modulo', 'modulo.avviso'])
            ->orderBy(['data_assegnazione' => SORT_DESC])
            ->all();

        return $this->render('rimborsi', [
            'rimborsi' => $rimborsi,
            'assegnazioni' => $assegnazioni,
        ]);
    }

    /**
     * Form nuovo rimborso spesa
     * @param int $assegnazione_id
     */
    public function actionNuovoRimborso($assegnazione_id)
    {
        $docente = $this->getDocenteLoggato();
        $assegnazione = $this->findAssegnazioneDocente($assegnazione_id, $docente);

        $model = new RimborsoSpesa();
        $model->assegnazione_id = $assegnazione->id;
        $model->status = RimborsoSpesa::STATUS_RICHIESTO;
        $model->data_spesa = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // Upload allegato
            $file = UploadedFile::getInstanceByName('allegato');
            if ($file) {
                $uploadPath = Yii::getAlias('@webroot/uploads/rimborsi');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $fileName = 'rimborso_' . $docente->codice_fiscale . '_' . time() . '.' . $file->extension;
                $filePath = $uploadPath . '/' . $fileName;
                if ($file->saveAs($filePath)) {
                    $model->allegato_path = 'uploads/rimborsi/' . $fileName;
                }
            }

            if ($model->save()) {
                // Salva dettagli voci di spesa
                $dettagliData = Yii::$app->request->post('Dettagli', []);
                if (!empty($dettagliData)) {
                    $model->salvaDettagli($dettagliData);
                }
                Yii::$app->session->setFlash('success', 'Richiesta di rimborso inviata con successo.');
                return $this->redirect(['rimborsi']);
            }
        }

        return $this->render('rimborso-form', [
            'model' => $model,
            'assegnazione' => $assegnazione,
            'isModifica' => false,
        ]);
    }

    /**
     * Modifica rimborso spesa (solo se status=richiesto)
     * @param int $id
     */
    public function actionModificaRimborso($id)
    {
        $docente = $this->getDocenteLoggato();
        $model = $this->findRimborsoDocente($id, $docente);

        if (!$model->isModificabile()) {
            throw new ForbiddenHttpException('Non puoi modificare un rimborso gia\' approvato o rifiutato.');
        }

        $assegnazione = $model->assegnazione;

        if ($model->load(Yii::$app->request->post())) {
            // Upload allegato (opzionale in modifica)
            $file = UploadedFile::getInstanceByName('allegato');
            if ($file) {
                $uploadPath = Yii::getAlias('@webroot/uploads/rimborsi');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $fileName = 'rimborso_' . $docente->codice_fiscale . '_' . time() . '.' . $file->extension;
                $filePath = $uploadPath . '/' . $fileName;
                if ($file->saveAs($filePath)) {
                    $model->allegato_path = 'uploads/rimborsi/' . $fileName;
                }
            }

            if ($model->save()) {
                // Salva dettagli voci di spesa
                $dettagliData = Yii::$app->request->post('Dettagli', []);
                if (!empty($dettagliData)) {
                    $model->salvaDettagli($dettagliData);
                }
                Yii::$app->session->setFlash('success', 'Rimborso aggiornato con successo.');
                return $this->redirect(['rimborsi']);
            }
        }

        return $this->render('rimborso-form', [
            'model' => $model,
            'assegnazione' => $assegnazione,
            'isModifica' => true,
        ]);
    }

    // =========================================================================
    // Contratti
    // =========================================================================

    /**
     * Lista contratti del docente loggato
     */
    public function actionContratti()
    {
        $docente = $this->getDocenteLoggato();

        $contratti = Contratto::find()
            ->alias('c')
            ->innerJoin('assegnazione a', 'a.id = c.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['c.deleted_at' => null])
            ->with(['assegnazione.modulo.avviso'])
            ->orderBy(['c.data_stipula' => SORT_DESC])
            ->all();

        return $this->render('contratti', [
            'contratti' => $contratti,
            'docente' => $docente,
        ]);
    }

    /**
     * Download PDF del contratto (solo se appartiene al docente loggato)
     * @param int $id
     */
    public function actionContrattoDownload($id)
    {
        $docente = $this->getDocenteLoggato();

        $contratto = Contratto::find()
            ->alias('c')
            ->innerJoin('assegnazione a', 'a.id = c.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['c.id' => $id])
            ->andWhere(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['c.deleted_at' => null])
            ->one();

        if (!$contratto) {
            throw new NotFoundHttpException('Contratto non trovato.');
        }

        // Controlla quale documento scaricare (priorità: firmato > generato)
        $tipo = Yii::$app->request->get('tipo', 'generato');
        $filePath = null;
        $fileName = 'contratto';

        if ($tipo === 'firmato' && !empty($contratto->documento_path)) {
            $filePath = Yii::getAlias('@webroot/' . $contratto->documento_path);
            $fileName = 'contratto_firmato_' . $contratto->id;
        } elseif (!empty($contratto->documento_generato_path)) {
            $filePath = Yii::getAlias('@webroot/' . $contratto->documento_generato_path);
            $fileName = 'contratto_' . $contratto->id;
        }

        if (!$filePath || !file_exists($filePath)) {
            throw new NotFoundHttpException('Documento non disponibile.');
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        return Yii::$app->response->sendFile($filePath, $fileName . '.' . $ext);
    }

    // =========================================================================
    // Documenti (Fatture/Cedolini)
    // =========================================================================

    /**
     * Lista documenti (richieste fatturazione/cedolini) del docente loggato
     */
    public function actionDocumenti()
    {
        $docente = $this->getDocenteLoggato();

        $richieste = RichiestaFatturazione::find()
            ->alias('rf')
            ->innerJoin('contratto c', 'c.id = rf.contratto_id AND c.deleted_at IS NULL')
            ->innerJoin('assegnazione a', 'a.id = c.assegnazione_id AND a.deleted_at IS NULL')
            ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale])
            ->andWhere(['rf.deleted_at' => null])
            ->with(['contratto.assegnazione.modulo'])
            ->orderBy(['rf.data_richiesta' => SORT_DESC])
            ->all();

        return $this->render('documenti', [
            'richieste' => $richieste,
            'docente' => $docente,
        ]);
    }

    // =========================================================================
    // Metodi helper per TwoFactorActionsTrait
    // =========================================================================

    protected function getTwoFactorIdentity()
    {
        return Yii::$app->docenteUser->identity;
    }

    protected function getTwoFactorUserComponent(): string
    {
        return 'docenteUser';
    }

    protected function getTwoFactorUserType(): string
    {
        return 'docente';
    }

    protected function getTwoFactorRedirectUrl(): array
    {
        return ['/docente-area/index'];
    }

    // =========================================================================
    // Metodi helper per ChangelogActionsTrait
    // =========================================================================

    protected function getChangelogUserType(): string
    {
        return 'docente';
    }

    protected function getChangelogIdentity()
    {
        return Yii::$app->docenteUser->identity;
    }

    // =========================================================================
    // Metodi helper per NotificheActionsTrait
    // =========================================================================

    protected function getNotificheUserType(): string
    {
        return 'docente';
    }

    protected function getNotificheUserId(): string
    {
        return Yii::$app->docenteUser->identity->docente_codice_fiscale;
    }

    // =========================================================================
    // Metodi helper privati
    // =========================================================================

    /**
     * Restituisce il docente attualmente loggato
     * @return Docente
     */
    private function getDocenteLoggato()
    {
        $auth = Yii::$app->docenteUser->identity;
        $docente = $auth ? $auth->docente : null;
        if (!$docente) {
            throw new NotFoundHttpException('Profilo docente non trovato per questo account.');
        }
        return $docente;
    }

    /**
     * Trova un'assegnazione verificando che appartenga al docente
     * @param int $id
     * @param Docente $docente
     * @return Assegnazione
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    private function findAssegnazioneDocente($id, $docente)
    {
        $assegnazione = Assegnazione::findOne($id);

        if (!$assegnazione) {
            throw new NotFoundHttpException('Assegnazione non trovata.');
        }

        if ($assegnazione->docente_codice_fiscale !== $docente->codice_fiscale) {
            throw new ForbiddenHttpException('Non hai accesso a questa assegnazione.');
        }

        return $assegnazione;
    }

    /**
     * Trova un rimborso spesa verificando che appartenga al docente
     * @param int $id
     * @param Docente $docente
     * @return RimborsoSpesa
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    private function findRimborsoDocente($id, $docente)
    {
        $model = RimborsoSpesa::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Rimborso spesa non trovato.');
        }

        $assegnazione = $model->assegnazione;
        if (!$assegnazione || $assegnazione->docente_codice_fiscale !== $docente->codice_fiscale) {
            throw new ForbiddenHttpException('Non hai accesso a questo rimborso.');
        }

        return $model;
    }

    /**
     * Trova una rendicontazione ore verificando che appartenga al docente
     * @param int $id
     * @param Docente $docente
     * @return RendicontazioneOre
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    private function findRendicontazioneOreDocente($id, $docente)
    {
        $model = RendicontazioneOre::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Registrazione ore non trovata.');
        }

        $assegnazione = $model->assegnazione;
        if (!$assegnazione || $assegnazione->docente_codice_fiscale !== $docente->codice_fiscale) {
            throw new ForbiddenHttpException('Non hai accesso a questa registrazione.');
        }

        return $model;
    }

    /**
     * Lista lezioni con presenze da compilare (area tutor)
     */
    public function actionPresenze()
    {
        $docente = Yii::$app->docenteUser->identity->docente;

        $lezioni = Calendario::findWithDeleted()
            ->alias('cal')
            ->leftJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('assegnazione a', 'a.modulo_id = m.id AND a.docente_codice_fiscale = :cf AND a.deleted_at IS NULL', [':cf' => $docente->codice_fiscale])
            ->leftJoin('presenza_allievo pa', 'pa.calendario_id = cal.id AND pa.deleted_at IS NULL')
            ->where(['cal.deleted_at' => null])
            ->andWhere(['OR',
                ['cal.docente_codice_fiscale' => $docente->codice_fiscale],
                ['NOT', ['a.id' => null]],
            ])
            ->select(['cal.*', 'COUNT(DISTINCT pa.id) AS presenze_acquisite', 'COUNT(DISTINCT a.id) AS ha_assegnazione'])
            ->groupBy('cal.id')
            ->orderBy(['cal.data' => SORT_DESC, 'cal.ora_inizio' => SORT_DESC])
            ->asArray()
            ->all();

        // Conta iscritti per modulo (per badge X/Y)
        $moduliIscritti = [];
        foreach ($lezioni as $lezione) {
            $modId = $lezione['modulo_id'];
            if (!isset($moduliIscritti[$modId])) {
                $moduliIscritti[$modId] = Iscrizione::find()
                    ->where(['modulo_id' => $modId])
                    ->andWhere(['deleted_at' => null])
                    ->count();
            }
        }

        // Ricarica come oggetti per usare le relazioni
        $calendarioIds = array_column($lezioni, 'id');
        $calendari = [];
        if (!empty($calendarioIds)) {
            $calendariObj = Calendario::findWithDeleted()
                ->alias('cal')
                ->where(['cal.id' => $calendarioIds, 'cal.deleted_at' => null])
                ->all();
            foreach ($calendariObj as $c) {
                $calendari[$c->id] = $c;
            }
        }

        return $this->render('presenze', [
            'docente' => $docente,
            'lezioni' => $lezioni,
            'calendari' => $calendari,
            'moduliIscritti' => $moduliIscritti,
        ]);
    }

    /**
     * Form presenze per una singola lezione
     */
    public function actionPresenzeLezione($calendario_id)
    {
        $docente = Yii::$app->docenteUser->identity->docente;

        $calendario = Calendario::findWithDeleted()
            ->alias('cal')
            ->leftJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('assegnazione a', 'a.modulo_id = m.id AND a.docente_codice_fiscale = :cf AND a.deleted_at IS NULL', [':cf' => $docente->codice_fiscale])
            ->where(['cal.id' => $calendario_id, 'cal.deleted_at' => null])
            ->andWhere(['OR',
                ['cal.docente_codice_fiscale' => $docente->codice_fiscale],
                ['NOT', ['a.id' => null]],
            ])
            ->one();

        if (!$calendario) {
            throw new NotFoundHttpException('Lezione non trovata o non accessibile.');
        }

        // Iscrizioni attive del modulo
        $iscrizioni = Iscrizione::findWithDeleted()
            ->alias('i')
            ->leftJoin('allievo al', 'al.codice_fiscale = i.allievo_codice_fiscale AND al.deleted_at IS NULL')
            ->where(['i.modulo_id' => $calendario->modulo_id, 'i.deleted_at' => null])
            ->orderBy(['al.cognome' => SORT_ASC, 'al.nome' => SORT_ASC])
            ->with('allievo')
            ->all();

        // Presenze esistenti indicizzate per CF
        $presenzeEsistenti = [];
        $presenzeRows = PresenzaAllievo::findWithDeleted()
            ->where(['calendario_id' => $calendario_id, 'deleted_at' => null])
            ->all();
        foreach ($presenzeRows as $p) {
            $presenzeEsistenti[$p->allievo_codice_fiscale] = $p;
        }

        return $this->render('_presenze-lezione', [
            'docente' => $docente,
            'calendario' => $calendario,
            'iscrizioni' => $iscrizioni,
            'presenzeEsistenti' => $presenzeEsistenti,
        ]);
    }

    /**
     * Salva presenze docente + studenti per una lezione (POST)
     */
    public function actionSalvaPresenze($calendario_id)
    {
        $docente = Yii::$app->docenteUser->identity->docente;

        $calendario = Calendario::findWithDeleted()
            ->alias('cal')
            ->leftJoin('modulo m', 'm.id = cal.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('assegnazione a', 'a.modulo_id = m.id AND a.docente_codice_fiscale = :cf AND a.deleted_at IS NULL', [':cf' => $docente->codice_fiscale])
            ->where(['cal.id' => $calendario_id, 'cal.deleted_at' => null])
            ->andWhere(['OR',
                ['cal.docente_codice_fiscale' => $docente->codice_fiscale],
                ['NOT', ['a.id' => null]],
            ])
            ->one();

        if (!$calendario) {
            throw new NotFoundHttpException('Lezione non trovata o non accessibile.');
        }

        $post = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Salva presenza docente
            $docentePresente = $post['docente_presente'] ?? '';
            $calendario->docente_presente = ($docentePresente === '') ? null : (int)$docentePresente;
            $calendario->docente_ore_effettive = isset($post['docente_ore_effettive']) && $post['docente_ore_effettive'] !== '' ? (float)$post['docente_ore_effettive'] : null;
            $calendario->save(false);

            // Salva presenze studenti
            $studenti = $post['studenti'] ?? [];
            foreach ($studenti as $cf => $dati) {
                $presenza = PresenzaAllievo::find()
                    ->where(['calendario_id' => $calendario_id, 'allievo_codice_fiscale' => $cf, 'deleted_at' => null])
                    ->one();

                if (!$presenza) {
                    $presenza = new PresenzaAllievo();
                    $presenza->calendario_id = (int)$calendario_id;
                    $presenza->allievo_codice_fiscale = $cf;
                }

                $presenteVal = $dati['presente'] ?? '';
                $presenza->presente = ($presenteVal === '') ? null : (int)$presenteVal;
                $presenza->ore_presenza = isset($dati['ore_presenza']) && $dati['ore_presenza'] !== '' ? (float)$dati['ore_presenza'] : null;
                $presenza->note = $dati['note'] ?? null;
                $presenza->save(false);
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Presenze salvate con successo.');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'presenze');
            Yii::$app->session->setFlash('error', 'Errore nel salvataggio delle presenze: ' . $e->getMessage());
        }

        return $this->redirect(['/docente-area/presenze']);
    }
}
