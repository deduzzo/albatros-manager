<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use app\models\DocenteAuth;
use app\models\AllievoAuth;
use app\models\DocenteAuthSearch;
use app\models\AllievoAuthSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * UtenteController - Gestione utenti admin, docenti e studenti.
 * Accesso riservato agli amministratori.
 */
class UtenteController extends Controller
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
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('Accesso riservato agli amministratori.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-status' => ['POST'],
                    'reset-2fa' => ['POST'],
                    'toggle-status-auth' => ['POST'],
                    'force-change-auth' => ['POST'],
                    'reset-2fa-auth' => ['POST'],
                ],
            ],
        ];
    }

    // --- CRUD Utenti Admin ---

    /**
     * Lista utenti con 3 tab: Admin, Docenti, Studenti.
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $docentiSearchModel = new DocenteAuthSearch();
        $docentiDataProvider = $docentiSearchModel->search(Yii::$app->request->queryParams);

        $studentiSearchModel = new AllievoAuthSearch();
        $studentiDataProvider = $studentiSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'docentiSearchModel' => $docentiSearchModel,
            'docentiDataProvider' => $docentiDataProvider,
            'studentiSearchModel' => $studentiSearchModel,
            'studentiDataProvider' => $studentiDataProvider,
        ]);
    }

    /**
     * Dettaglio utente admin.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creazione nuovo utente admin.
     */
    public function actionCreate()
    {
        $model = new User();
        $model->status = User::STATUS_ACTIVE;
        $model->generateAuthKey();

        if ($model->load(Yii::$app->request->post())) {
            $password = Yii::$app->request->post('password');
            if (!empty($password)) {
                $model->setPassword($password);
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Utente creato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Modifica utente admin.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Impedisce auto-declassamento
            if (!$model->canChangeRoleBy(Yii::$app->user->identity)) {
                $model->ruolo = User::RUOLO_ADMIN;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Utente aggiornato con successo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Eliminazione utente admin (soft delete).
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->canBeDeletedBy(Yii::$app->user->identity)) {
            Yii::$app->session->setFlash('danger', 'Non puoi eliminare il tuo stesso account.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Utente eliminato con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Attiva/disattiva utente admin.
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);

        if (!$model->canBeDeletedBy(Yii::$app->user->identity)) {
            Yii::$app->session->setFlash('danger', 'Non puoi disabilitare il tuo stesso account.');
            return $this->redirect(['index']);
        }

        $model->status = $model->status === User::STATUS_ACTIVE
            ? User::STATUS_INACTIVE
            : User::STATUS_ACTIVE;
        $model->save(false, ['status']);

        $stato = $model->status === User::STATUS_ACTIVE ? 'attivato' : 'disabilitato';
        Yii::$app->session->setFlash('success', "Utente {$stato} con successo.");
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reset password utente admin.
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('new_password');
            $forceChange = (bool)Yii::$app->request->post('force_change', 0);

            if (!empty($password)) {
                $model->resetPassword($password, $forceChange);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reimpostata con successo.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                Yii::$app->session->setFlash('danger', 'Inserisci una nuova password.');
            }
        }

        return $this->render('reset-password', ['model' => $model]);
    }

    /**
     * Reset 2FA utente admin.
     */
    public function actionReset2fa($id)
    {
        $model = $this->findModel($id);
        $model->disableTotp();
        Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disabilitata per ' . ($model->nominativo ?: $model->username) . '.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    // --- Gestione Auth Docenti/Studenti ---

    /**
     * Dettaglio credenziali docente/studente.
     */
    public function actionViewAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        return $this->render('view-auth', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    /**
     * Reset password docente/studente.
     */
    public function actionResetPasswordAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('new_password');
            $forceChange = (bool)Yii::$app->request->post('force_change', 0);

            if (!empty($password)) {
                $model->resetPassword($password, $forceChange);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reimpostata con successo.');
                    return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
                }
            } else {
                Yii::$app->session->setFlash('danger', 'Inserisci una nuova password.');
            }
        }

        return $this->render('reset-password-auth', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    /**
     * Attiva/sospende account docente/studente.
     */
    public function actionToggleStatusAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);

        $activeStatus = ($type === 'docente') ? DocenteAuth::STATUS_ACTIVE : AllievoAuth::STATUS_ACTIVE;
        $suspendedStatus = ($type === 'docente') ? DocenteAuth::STATUS_SUSPENDED : AllievoAuth::STATUS_SUSPENDED;

        $model->status = $model->status === $activeStatus ? $suspendedStatus : $activeStatus;
        $model->save(false, ['status']);

        $stato = $model->status === $activeStatus ? 'attivato' : 'sospeso';
        Yii::$app->session->setFlash('success', "Account {$stato} con successo.");
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    /**
     * Forza cambio password docente/studente.
     */
    public function actionForceChangeAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        $model->force_password_change = 1;
        $model->save(false, ['force_password_change']);

        Yii::$app->session->setFlash('success', 'Cambio password obbligatorio impostato.');
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    /**
     * Reset 2FA docente/studente.
     */
    public function actionReset2faAuth($type, $id)
    {
        $model = $this->findAuthModel($type, $id);
        $model->disableTotp();
        Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disabilitata.');
        return $this->redirect(['view-auth', 'type' => $type, 'id' => $id]);
    }

    // --- Helpers ---

    /**
     * Trova utente admin per ID.
     *
     * @param int $id
     * @return User
     * @throws NotFoundHttpException
     */
    protected function findModel($id): User
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Utente non trovato.');
        }
        return $model;
    }

    /**
     * Trova model auth docente/studente per tipo e ID.
     *
     * @param string $type 'docente' o 'studente'
     * @param int $id
     * @return DocenteAuth|AllievoAuth
     * @throws NotFoundHttpException
     */
    protected function findAuthModel(string $type, $id)
    {
        $model = match ($type) {
            'docente' => DocenteAuth::findOne($id),
            'studente' => AllievoAuth::findOne($id),
            default => throw new NotFoundHttpException('Tipo utente non valido.'),
        };

        if ($model === null) {
            throw new NotFoundHttpException('Account non trovato.');
        }
        return $model;
    }
}
