<?php

namespace app\components\traits;

use Yii;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

/**
 * Trait per gestione TOTP (Google Authenticator).
 * Usato da User, DocenteAuth, AllievoAuth.
 *
 * Richiede che il model abbia i campi:
 * - totp_secret (VARCHAR 255, nullable)
 * - totp_enabled (TINYINT, default 0)
 * - totp_enabled_at (DATETIME, nullable)
 */
trait TotpTrait
{
    private ?TwoFactorAuth $_tfa = null;

    protected function getTfa(): TwoFactorAuth
    {
        if ($this->_tfa === null) {
            $issuer = Yii::$app->params['totpIssuer'] ?? 'Albatros Manager';
            $this->_tfa = new TwoFactorAuth(issuer: $issuer, qrcodeprovider: new QRServerProvider());
        }
        return $this->_tfa;
    }

    /**
     * Genera un nuovo TOTP secret e lo salva criptato nel model.
     * NON salva il record (chiamare save() separatamente).
     */
    public function generateTotpSecret(): string
    {
        $tfa = $this->getTfa();
        $secret = $tfa->createSecret();
        $this->totp_secret = $this->encryptTotpSecret($secret);
        return $secret;
    }

    /**
     * Restituisce il secret TOTP decriptato.
     */
    public function getDecryptedTotpSecret(): ?string
    {
        if (empty($this->totp_secret)) {
            return null;
        }
        return $this->decryptTotpSecret($this->totp_secret);
    }

    /**
     * Genera URL per QR code dato un secret in chiaro.
     */
    public function getTotpQrCodeUrl(string $plainSecret): string
    {
        $tfa = $this->getTfa();
        $label = $this->getTotpLabel();
        return $tfa->getQRCodeImageAsDataUri($label, $plainSecret);
    }

    /**
     * Abilita TOTP verificando il codice fornito.
     * Salva il record.
     */
    public function enableTotp(string $code): bool
    {
        $secret = $this->getDecryptedTotpSecret();
        if ($secret === null) {
            return false;
        }

        $tfa = $this->getTfa();
        if (!$tfa->verifyCode($secret, $code, 1)) {
            return false;
        }

        $this->totp_enabled = 1;
        $this->totp_enabled_at = date('Y-m-d H:i:s');
        return $this->save(false, ['totp_enabled', 'totp_enabled_at']);
    }

    /**
     * Disabilita TOTP e rimuove il secret.
     */
    public function disableTotp(): bool
    {
        $this->totp_secret = null;
        $this->totp_enabled = 0;
        $this->totp_enabled_at = null;
        return $this->save(false, ['totp_secret', 'totp_enabled', 'totp_enabled_at']);
    }

    /**
     * Verifica un codice TOTP.
     * discrepancy=1 → tolleranza ±30 secondi per clock drift.
     */
    public function verifyTotp(string $code): bool
    {
        $secret = $this->getDecryptedTotpSecret();
        if ($secret === null) {
            return false;
        }

        $tfa = $this->getTfa();
        return $tfa->verifyCode($secret, $code, 1);
    }

    public function isTotpEnabled(): bool
    {
        return (int)$this->totp_enabled === 1;
    }

    /**
     * Label per il QR code (es: "email" o "codice_fiscale").
     */
    protected function getTotpLabel(): string
    {
        if (isset($this->email) && !empty($this->email)) {
            return $this->email;
        }
        if (isset($this->docente_codice_fiscale)) {
            return $this->docente_codice_fiscale;
        }
        if (isset($this->allievo_codice_fiscale)) {
            return $this->allievo_codice_fiscale;
        }
        return (string)$this->getPrimaryKey();
    }

    private function encryptTotpSecret(string $secret): string
    {
        $key = Yii::$app->params['totpEncryptionKey'] ?? 'default-key';
        return base64_encode(Yii::$app->security->encryptByKey($secret, $key));
    }

    private function decryptTotpSecret(string $encrypted): ?string
    {
        $key = Yii::$app->params['totpEncryptionKey'] ?? 'default-key';
        $decrypted = Yii::$app->security->decryptByKey(base64_decode($encrypted), $key);
        return $decrypted === false ? null : $decrypted;
    }
}
