<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\DocenteAuth;
use app\models\Docente;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Istanza;
use app\models\Assegnazione;
use app\models\Contratto;
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
                        'actions' => ['index', 'logout', 'profilo', 'bandi', 'bando', 'iscrizione', 'le-mie-iscrizioni', 'iscrizione-view', 'i-miei-contratti', 'contratto-view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['POST'],
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

        return $this->render('index', [
            'docente' => $docente,
            'istanzeCount' => $istanzeCount,
            'bandiAperti' => $bandiAperti,
            'moduliAperti' => $moduliAperti,
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
}
