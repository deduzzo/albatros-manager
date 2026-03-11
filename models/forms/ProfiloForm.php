<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * Form per modifica profilo utente admin.
 */
class ProfiloForm extends Model
{
    public $username;
    public $email;

    private $_user;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        $this->username = $user->username;
        $this->email = $user->email;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['username'], 'unique', 'targetClass' => User::class, 'filter' => ['!=', 'id', $this->_user->id],
                'message' => 'Questo username è già in uso.'],
            [['email'], 'unique', 'targetClass' => User::class, 'filter' => ['!=', 'id', $this->_user->id],
                'message' => 'Questa email è già in uso.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
        ];
    }

    public function salva()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->username = $this->username;
        $this->_user->email = $this->email;

        return $this->_user->save(false);
    }

    public function getUser()
    {
        return $this->_user;
    }
}
