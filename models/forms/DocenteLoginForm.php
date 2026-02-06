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
            [['codice_fiscale', 'password'], 'required'],
            [['codice_fiscale'], 'string', 'length' => 16],
            [['codice_fiscale'], 'filter', 'filter' => 'strtoupper'],
            [['rememberMe'], 'boolean'],
            ['password', 'validatePassword'],
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
     * Valida la password
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $auth = $this->getAuth();

            if (!$auth) {
                $this->addError($attribute, 'Codice fiscale o password errati.');
            } elseif (!$auth->isActive()) {
                if ($auth->status === DocenteAuth::STATUS_PENDING) {
                    $this->addError($attribute, 'Account non ancora verificato. Controlla la tua email.');
                } else {
                    $this->addError($attribute, 'Account sospeso. Contatta l\'amministrazione.');
                }
            } elseif (!$auth->validatePassword($this->password)) {
                $this->addError($attribute, 'Codice fiscale o password errati.');
            }
        }
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
