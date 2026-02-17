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
use app\models\forms\DocenteLoginForm;
use app\models\forms\DocenteRegistrazioneForm;
use app\models\forms\IscrizioneForm;
use yii\web\UploadedFile;

/**
 * Controller per l'area docenti.
 */
class DocenteAreaController extends Controller
{
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

        // Solo per azioni protette (non login/registrazione)
        $guestActions = ['login', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password'];
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
                        'actions' => ['login', 'registrazione', 'verifica-email', 'password-dimenticata', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'logout', 'profilo', 'bandi', 'bando', 'iscrizione', 'le-mie-iscrizioni', 'iscrizione-view', 'i-miei-contratti', 'contratto-view', 'le-mie-assegnazioni', 'inserisci-ore', 'le-mie-ore', 'modifica-ore', 'elimina-ore'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'elimina-ore' => ['POST'],
                ],
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
     * Login docente
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->docenteUser->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new DocenteLoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
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
}
