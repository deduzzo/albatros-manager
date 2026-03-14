<?php

namespace app\components\traits;

use Yii;
use app\models\Notifica;
use yii\data\ActiveDataProvider;

/**
 * Trait condiviso per le actions notifiche nei controller.
 *
 * Il controller che usa questo trait deve implementare:
 * - getNotificheUserType(): string — 'admin', 'docente', 'studente'
 * - getNotificheUserId(): string — ID (admin) o CF (docente/studente)
 */
trait NotificheActionsTrait
{
    public function actionNotifiche()
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();
        $tab = Yii::$app->request->get('tab', 'non-lette');

        $query = Notifica::queryPerUtente($userType, $userId);

        if ($tab === 'non-lette') {
            $query->leftJoin('notifica_lettura nl', 'nl.notifica_id = notifica.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])->andWhere(['nl.id' => null]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $nonLetteCount = Notifica::countNonLette($userType, $userId);

        return $this->render('@app/views/site/notifiche', [
            'dataProvider' => $dataProvider,
            'tab' => $tab,
            'nonLetteCount' => $nonLetteCount,
            'userType' => $userType,
            'userId' => $userId,
        ]);
    }

    public function actionNotificaView($id)
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifica = Notifica::queryPerUtente($userType, $userId)
            ->andWhere(['notifica.id' => $id])
            ->one();

        if ($notifica === null) {
            throw new \yii\web\NotFoundHttpException('Notifica non trovata.');
        }

        // Segna come letta
        $notifica->segnaComeLetta($userType, $userId);

        return $this->render('@app/views/site/notifica-view', [
            'notifica' => $notifica,
            'userType' => $userType,
            'userId' => $userId,
        ]);
    }

    public function actionSegnaLetta($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifica = Notifica::findOne($id);
        if ($notifica) {
            $notifica->segnaComeLetta($userType, $userId);
        }

        return ['success' => true];
    }

    public function actionSegnaTutteLette()
    {
        $userType = $this->getNotificheUserType();
        $userId = $this->getNotificheUserId();

        $notifiche = Notifica::queryPerUtente($userType, $userId)->all();
        foreach ($notifiche as $notifica) {
            $notifica->segnaComeLetta($userType, $userId);
        }

        Yii::$app->session->setFlash('success', 'Tutte le notifiche segnate come lette.');
        return $this->redirect(['notifiche']);
    }
}
