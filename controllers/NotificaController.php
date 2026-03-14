<?php

namespace app\controllers;

use Yii;
use app\models\Notifica;
use app\models\NotificaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * NotificaController - Gestione notifiche admin.
 * Accesso riservato agli amministratori.
 */
class NotificaController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lista notifiche.
     */
    public function actionIndex()
    {
        $searchModel = new NotificaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creazione nuova notifica.
     */
    public function actionCreate()
    {
        $model = new Notifica();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Notifica creata con successo.');

            // Invio email se richiesto
            if ($model->invia_email) {
                $this->inviaEmail($model);
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Dettaglio notifica con letture.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $letture = $model->getLetture()->orderBy(['letta_at' => SORT_DESC])->all();

        return $this->render('view', [
            'model' => $model,
            'letture' => $letture,
        ]);
    }

    /**
     * Eliminazione notifica (soft delete).
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Notifica eliminata con successo.');
        return $this->redirect(['index']);
    }

    /**
     * Invio email ai destinatari della notifica.
     */
    private function inviaEmail(Notifica $model): void
    {
        set_time_limit(0);
        $emails = $model->getEmailDestinatari();
        $sent = $failed = 0;

        foreach ($emails as $email) {
            try {
                Yii::$app->mailer->compose(['html' => 'notifica-html', 'text' => 'notifica-text'], ['notifica' => $model])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setTo($email)
                    ->setSubject($model->titolo)
                    ->send();
                $sent++;
            } catch (\Throwable $e) {
                Yii::error("Email notifica #{$model->id} a {$email}: " . $e->getMessage());
                $failed++;
            }
        }

        $model->email_inviata = 1;
        $model->save(false, ['email_inviata']);
        Yii::$app->session->setFlash('info', "Email: {$sent} inviate" . ($failed > 0 ? ", {$failed} fallite" : ''));
    }

    /**
     * Trova notifica per ID.
     *
     * @param int $id
     * @return Notifica
     * @throws NotFoundHttpException
     */
    protected function findModel($id): Notifica
    {
        $model = Notifica::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Notifica non trovata.');
        }
        return $model;
    }
}
