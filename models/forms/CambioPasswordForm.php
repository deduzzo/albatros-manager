<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * Form per cambio password utente admin.
 */
class CambioPasswordForm extends Model
{
    public $password_attuale;
    public $nuova_password;
    public $conferma_password;

    private $_user;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['password_attuale', 'nuova_password', 'conferma_password'], 'required'],
            [['nuova_password'], 'string', 'min' => 6],
            [['conferma_password'], 'compare', 'compareAttribute' => 'nuova_password',
                'message' => 'Le password non coincidono.'],
            [['password_attuale'], 'validatePasswordAttuale'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password_attuale' => 'Password attuale',
            'nuova_password' => 'Nuova password',
            'conferma_password' => 'Conferma nuova password',
        ];
    }

    public function validatePasswordAttuale($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user->validatePassword($this->password_attuale)) {
                $this->addError($attribute, 'La password attuale non è corretta.');
            }
        }
    }

    public function salva()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->setPassword($this->nuova_password);
        return $this->_user->save(false);
    }
}
