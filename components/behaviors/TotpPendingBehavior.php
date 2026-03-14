<?php

namespace app\components\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * Behavior che impedisce la navigazione quando la verifica 2FA è pendente.
 * Se in sessione esiste totp_pending_user_id + totp_pending_type,
 * redirige a verifica-2fa per tutte le action non consentite.
 */
class TotpPendingBehavior extends Behavior
{
    /**
     * @var string Action per la verifica 2FA
     */
    public string $verifyAction = 'verifica-2fa';

    /**
     * @var array Actions sempre permesse in stato 2FA pendente
     */
    public array $allowedActions = ['login', 'logout', 'verifica-2fa'];

    /**
     * @var string Tipo utente per il filtro sessione (admin, docente, studente)
     */
    public string $userType = 'admin';

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction(ActionEvent $event): void
    {
        $session = Yii::$app->session;
        $pendingType = $session->get('totp_pending_type');
        $pendingUserId = $session->get('totp_pending_user_id');

        if ($pendingType !== $this->userType || $pendingUserId === null) {
            return;
        }

        $currentAction = $event->action->id;
        if (in_array($currentAction, $this->allowedActions, true)) {
            return;
        }

        $controller = $event->action->controller;
        $event->isValid = false;
        $controller->redirect([$this->verifyAction]);
    }
}
