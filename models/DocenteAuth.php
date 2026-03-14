<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use app\components\base\BaseActiveRecord;
use app\components\traits\TotpTrait;

/**
 * Model per la tabella docente_auth.
 * Gestisce l'autenticazione dei docenti separatamente dagli utenti admin.
 *
 * @property int $id
 * @property string $docente_codice_fiscale
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $email_verify_token
 * @property string|null $password_reset_token
 * @property string $status
 * @property string|null $email_verified_at
 * @property string|null $last_login_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Docente $docente
 */
class DocenteAuth extends BaseActiveRecord implements IdentityInterface
{
    use TotpTrait;

    const STATUS_PENDING = 'pending_verification';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    public static function tableName()
    {
        return '{{%docente_auth}}';
    }

    public function rules()
    {
        return [
            [['docente_codice_fiscale', 'password_hash', 'auth_key'], 'required'],
            [['docente_codice_fiscale'], 'string', 'max' => 16],
            [['password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email_verify_token', 'password_reset_token'], 'string', 'max' => 64],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_SUSPENDED]],
            [['email_verified_at', 'last_login_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['docente_codice_fiscale'], 'unique'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            ['force_password_change', 'integer'],
            ['totp_enabled', 'integer'],
            [['totp_secret', 'totp_enabled_at', 'last_changelog_seen'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'docente_codice_fiscale' => 'Codice Fiscale',
            'status' => 'Stato',
            'email_verified_at' => 'Email Verificata',
            'last_login_at' => 'Ultimo Accesso',
            'created_at' => 'Registrato il',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE, 'deleted_at' => null]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; // Non usiamo access token
    }

    /**
     * Trova docente per codice fiscale
     */
    public static function findByCodiceFiscale($cf)
    {
        return static::findOne(['docente_codice_fiscale' => strtoupper($cf), 'deleted_at' => null]);
    }

    /**
     * Trova docente per token di verifica email
     */
    public static function findByEmailVerifyToken($token)
    {
        return static::findOne([
            'email_verify_token' => $token,
            'status' => self::STATUS_PENDING,
            'deleted_at' => null,
        ]);
    }

    /**
     * Trova docente per token reset password
     */
    public static function findByPasswordResetToken($token)
    {
        if (empty($token)) {
            return null;
        }

        $auth = static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
            'deleted_at' => null,
        ]);

        if (!$auth || !$auth->isPasswordResetTokenValid()) {
            return null;
        }

        return $auth;
    }

    /**
     * Verifica se il token reset password è ancora valido (24 ore)
     */
    public function isPasswordResetTokenValid()
    {
        if (empty($this->password_reset_token)) {
            return false;
        }

        $parts = explode('_', $this->password_reset_token);
        $timestamp = (int) end($parts);
        $expire = 86400; // 24 ore

        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Valida la password
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Imposta la password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Genera auth key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }

    /**
     * Genera token verifica email
     */
    public function generateEmailVerifyToken()
    {
        $this->email_verify_token = Yii::$app->security->generateRandomString(64);
    }

    /**
     * Genera token reset password
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString(32) . '_' . time();
    }

    /**
     * Rimuove token reset password
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Conferma email
     */
    public function confirmEmail()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->email_verify_token = null;
        $this->email_verified_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Registra ultimo login
     */
    public function updateLastLogin()
    {
        $this->last_login_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocente()
    {
        return $this->hasOne(Docente::class, ['codice_fiscale' => 'docente_codice_fiscale']);
    }

    /**
     * Restituisce label status
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_PENDING => 'In attesa verifica',
            self::STATUS_ACTIVE => 'Attivo',
            self::STATUS_SUSPENDED => 'Sospeso',
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_PENDING => 'bg-orange text-orange-fg',
            self::STATUS_ACTIVE => 'bg-green text-green-fg',
            self::STATUS_SUSPENDED => 'bg-red text-red-fg',
        ];
        return $classes[$this->status] ?? 'bg-default text-default-fg';
    }

    /**
     * Verifica se l'account è attivo
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Attributi sensibili da mascherare nel audit log
     */
    protected function auditMaskedAttributes()
    {
        return ['password_hash', 'auth_key', 'email_verify_token', 'password_reset_token', 'totp_secret'];
    }

    /**
     * Reimposta la password con opzione di forzare il cambio al prossimo login.
     */
    public function resetPassword(string $newPassword, bool $forceChange = true): void
    {
        $this->setPassword($newPassword);
        $this->force_password_change = $forceChange ? 1 : 0;
        $this->removePasswordResetToken();
    }
}
