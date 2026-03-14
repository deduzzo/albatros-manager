<?php

namespace app\components\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * Behavior che forza il cambio password al prossimo login.
 * Intercetta beforeAction e redirige a cambio password se il flag è attivo.
 */
class ForcePasswordChangeBehavior extends Behavior
{
    /**
     * @var string Nome dell'action per il cambio password
     */
    public string $changePasswordAction = 'cambia-password';

    /**
     * @var array Actions sempre permesse anche con flag attivo
     */
    public array $allowedActions = ['logout', 'cambia-password'];

    /**
     * @var string Nome del componente user Yii (user, docenteUser, studenteUser)
     */
    public string $userComponent = 'user';

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction(ActionEvent $event): void
    {
        $user = Yii::$app->get($this->userComponent);

        if ($user->isGuest) {
            return;
        }

        $identity = $user->identity;
        if ($identity === null) {
            return;
        }

        if (!isset($identity->force_password_change) || (int)$identity->force_password_change !== 1) {
            return;
        }

        $currentAction = $event->action->id;
        if (in_array($currentAction, $this->allowedActions, true)) {
            return;
        }

        $event->isValid = false;
        Yii::$app->session->setFlash('warning', 'Devi cambiare la password prima di continuare.');
        Yii::$app->response->redirect([$this->changePasswordAction]);
        Yii::$app->response->send();
    }
}
