<?php

namespace app\components\traits;

use Yii;
use app\models\TotpAttempt;

/**
 * Trait condiviso per le actions 2FA nei controller.
 * Usato da SiteController, DocenteAreaController, StudenteAreaController.
 *
 * Il controller che usa questo trait deve implementare:
 * - getTwoFactorIdentity(): restituisce il model auth corrente
 * - getTwoFactorUserComponent(): restituisce il nome del componente user (es. 'user', 'docenteUser')
 * - getTwoFactorUserType(): restituisce il tipo utente ('admin', 'docente', 'studente')
 * - getTwoFactorRedirectUrl(): URL redirect dopo login 2FA riuscito
 */
trait TwoFactorActionsTrait
{
    /**
     * Attivazione 2FA: genera secret, mostra QR, verifica codice per abilitare.
     */
    public function actionAttiva2fa()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        $secret = null;
        $qrCodeUrl = null;
        $error = null;

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            if ($identity->enableTotp($code)) {
                Yii::$app->session->setFlash('success', 'Autenticazione a due fattori attivata con successo.');
                return $this->redirect(['profilo']);
            } else {
                $error = 'Codice non valido. Riprova.';
                // Ripristina il secret per mostrare di nuovo il QR
                $secret = $identity->getDecryptedTotpSecret();
                if ($secret) {
                    $qrCodeUrl = $identity->getTotpQrCodeUrl($secret);
                }
            }
        } else {
            // Genera secret solo se non ne esiste uno (evita invalidazione su refresh)
            if (empty($identity->totp_secret) || $identity->isTotpEnabled()) {
                $secret = $identity->generateTotpSecret();
                $identity->save(false, ['totp_secret']);
            } else {
                $secret = $identity->getDecryptedTotpSecret();
            }
            $qrCodeUrl = $identity->getTotpQrCodeUrl($secret);
        }

        return $this->render('@app/views/site/attiva-2fa', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'error' => $error,
        ]);
    }

    /**
     * Disattivazione 2FA: verifica codice corrente per disabilitare.
     */
    public function actionDisattiva2fa()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            if ($identity->verifyTotp($code)) {
                $identity->disableTotp();
                Yii::$app->session->setFlash('success', 'Autenticazione a due fattori disattivata.');
                return $this->redirect(['profilo']);
            } else {
                Yii::$app->session->setFlash('danger', 'Codice non valido.');
            }
        }

        return $this->render('@app/views/site/disattiva-2fa');
    }

    /**
     * Verifica 2FA post-login: controlla il codice TOTP prima di completare il login.
     * Usa layout login (pagina pre-autenticazione).
     */
    public function actionVerifica2fa()
    {
        // Usa layout login (pagina pre-autenticazione)
        $this->layout = 'login';

        $session = Yii::$app->session;
        $pendingUserId = $session->get('totp_pending_user_id');
        $pendingType = $session->get('totp_pending_type');
        $userType = $this->getTwoFactorUserType();

        if ($pendingUserId === null || $pendingType !== $userType) {
            return $this->redirect(['login']);
        }

        $error = null;

        // Verifica blocco
        if (TotpAttempt::isLocked($userType, (string)$pendingUserId)) {
            $error = 'Troppi tentativi falliti. Riprova tra 5 minuti.';
            return $this->render('@app/views/site/verifica-2fa', [
                'error' => $error,
                'locked' => true,
            ]);
        }

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            // Trova l'identity
            $userComponent = $this->getTwoFactorUserComponent();
            $identityClass = Yii::$app->get($userComponent)->identityClass;
            $identity = $identityClass::findIdentity($pendingUserId);

            if ($identity !== null && $identity->verifyTotp($code)) {
                // Login riuscito
                TotpAttempt::resetAttempts($userType, (string)$pendingUserId);
                $session->remove('totp_pending_user_id');
                $session->remove('totp_pending_type');

                Yii::$app->get($userComponent)->login($identity);
                return $this->redirect($this->getTwoFactorRedirectUrl());
            } else {
                $remaining = TotpAttempt::registerFailedAttempt($userType, (string)$pendingUserId);
                if ($remaining <= 0) {
                    $error = 'Troppi tentativi falliti. Account bloccato per 5 minuti.';
                } else {
                    $error = "Codice non valido. {$remaining} tentativi rimasti.";
                }
            }
        }

        return $this->render('@app/views/site/verifica-2fa', [
            'error' => $error,
            'locked' => false,
        ]);
    }

    /**
     * Cambio password: forzato (layout login) o volontario.
     */
    public function actionCambiaPassword()
    {
        $identity = $this->getTwoFactorIdentity();
        if ($identity === null) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $currentPassword = Yii::$app->request->post('current_password');
            $newPassword = Yii::$app->request->post('new_password');
            $confirmPassword = Yii::$app->request->post('confirm_password');

            if (empty($newPassword) || $newPassword !== $confirmPassword) {
                Yii::$app->session->setFlash('danger', 'Le password non coincidono.');
            } elseif (strlen($newPassword) < 6) {
                Yii::$app->session->setFlash('danger', 'La password deve essere di almeno 6 caratteri.');
            } else {
                // Per force_password_change, non richiediamo la password corrente
                if ((int)$identity->force_password_change === 1 || $identity->validatePassword($currentPassword)) {
                    $identity->setPassword($newPassword);
                    $identity->force_password_change = 0;
                    if ($identity->save(false)) {
                        Yii::$app->session->setFlash('success', 'Password cambiata con successo.');
                        return $this->redirect(['profilo']);
                    }
                } else {
                    Yii::$app->session->setFlash('danger', 'Password corrente non valida.');
                }
            }
        }

        $isForced = (int)$identity->force_password_change === 1;
        if ($isForced) {
            $this->layout = 'login'; // Pre-autenticazione effettiva
        }
        return $this->render('@app/views/site/cambia-password', [
            'isForced' => $isForced,
        ]);
    }
}
