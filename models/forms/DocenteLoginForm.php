<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\DocenteAuth;

/**
 * Form per il login docente
 */
class DocenteLoginForm extends Model
{
    public $codice_fiscale;
    public $password;
    public $rememberMe = true;

    private $_auth = false;

    public function rules()
    {
        return [
            [['codice_fiscale'], 'required'],
            [['password'], 'string'],
            [['codice_fiscale'], 'string', 'length' => 16],
            [['codice_fiscale'], 'filter', 'filter' => 'strtoupper'],
            [['rememberMe'], 'boolean'],
            ['codice_fiscale', 'validateLogin'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'password' => 'Password',
            'rememberMe' => 'Ricordami',
        ];
    }

    /**
     * Valida il login: cerca o crea automaticamente l'account docente.
     * In modalita' sviluppo (YII_DEBUG), permette login con password vuota.
     */
    public function validateLogin($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $auth = $this->getAuth();

            // Se non esiste un account auth e siamo in debug, crealo al volo
            if (!$auth && YII_DEBUG) {
                $docente = \app\models\Docente::findOne(['codice_fiscale' => $this->codice_fiscale]);
                if ($docente) {
                    $auth = new DocenteAuth();
                    $auth->docente_codice_fiscale = $this->codice_fiscale;
                    $auth->setPassword($this->password ?: 'test');
                    $auth->generateAuthKey();
                    $auth->status = DocenteAuth::STATUS_ACTIVE;
                    $auth->email_verified_at = date('Y-m-d H:i:s');
                    if ($auth->save(false)) {
                        $this->_auth = $auth;
                    } else {
                        $this->addError($attribute, 'Errore nella creazione dell\'account.');
                        return;
                    }
                } else {
                    $this->addError($attribute, 'Codice fiscale non trovato nel sistema.');
                    return;
                }
            }

            if (!$auth) {
                $this->addError($attribute, 'Codice fiscale o password errati.');
            } elseif (!$auth->isActive()) {
                if ($auth->status === DocenteAuth::STATUS_PENDING) {
                    $this->addError($attribute, 'Account non ancora verificato. Controlla la tua email.');
                } else {
                    $this->addError($attribute, 'Account sospeso. Contatta l\'amministrazione.');
                }
            } elseif (!empty($this->password) && !$auth->validatePassword($this->password)) {
                $this->addError($attribute, 'Codice fiscale o password errati.');
            }
            // Se password vuota in DEBUG mode, salta la validazione password
        }
    }

    /**
     * Valida le credenziali senza effettuare il login.
     * Usato per il flusso 2FA: prima si validano le credenziali, poi se serve il 2FA.
     */
    public function validateCredentials(): bool
    {
        return $this->validate();
    }

    /**
     * Effettua il login
     */
    public function login()
    {
        if ($this->validate()) {
            $auth = $this->getAuth();
            $auth->updateLastLogin();

            return Yii::$app->docenteUser->login(
                $auth,
                $this->rememberMe ? 3600 * 24 * 30 : 0
            );
        }
        return false;
    }

    /**
     * Trova l'auth per codice fiscale
     */
    public function getAuth()
    {
        if ($this->_auth === false) {
            $this->_auth = DocenteAuth::findByCodiceFiscale($this->codice_fiscale);
        }

        return $this->_auth;
    }
}
