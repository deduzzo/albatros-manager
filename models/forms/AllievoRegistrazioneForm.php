<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Allievo;
use app\models\AllievoAuth;

/**
 * Form per la registrazione studente
 */
class AllievoRegistrazioneForm extends Model
{
    public $codice_fiscale;
    public $cognome;
    public $nome;
    public $email;
    public $telefono;
    public $data_nascita;
    public $luogo_nascita;
    public $indirizzo;
    public $cap;
    public $citta;
    public $provincia;
    public $password;
    public $password_confirm;
    public $privacy_policy;

    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome', 'email', 'password', 'password_confirm'], 'required'],
            [['codice_fiscale'], 'string', 'length' => 16],
            [['codice_fiscale'], 'filter', 'filter' => 'strtoupper'],
            [['codice_fiscale'], 'match', 'pattern' => '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i', 'message' => 'Codice fiscale non valido.'],
            [['codice_fiscale'], 'validateCodiceFiscaleNonRegistrato'],
            [['cognome', 'nome'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['email'], 'validateEmailNonRegistrata'],
            [['telefono'], 'string', 'max' => 20],
            [['data_nascita'], 'date', 'format' => 'php:Y-m-d'],
            [['luogo_nascita', 'citta'], 'string', 'max' => 100],
            [['indirizzo'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['password'], 'string', 'min' => 6],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message' => 'Le password non coincidono.'],
            [['privacy_policy'], 'required', 'requiredValue' => 1, 'message' => 'Devi accettare la Privacy Policy.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'cognome' => 'Cognome',
            'nome' => 'Nome',
            'email' => 'Email',
            'telefono' => 'Telefono',
            'data_nascita' => 'Data di Nascita',
            'luogo_nascita' => 'Luogo di Nascita',
            'indirizzo' => 'Indirizzo',
            'cap' => 'CAP',
            'citta' => 'Città',
            'provincia' => 'Provincia',
            'password' => 'Password',
            'password_confirm' => 'Conferma Password',
            'privacy_policy' => 'Privacy Policy',
        ];
    }

    public function validateCodiceFiscaleNonRegistrato($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = AllievoAuth::find()
                ->where(['allievo_codice_fiscale' => $this->codice_fiscale])
                ->exists();
            if ($exists) {
                $this->addError($attribute, 'Questo codice fiscale è già registrato.');
            }
        }
    }

    public function validateEmailNonRegistrata($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = Allievo::find()
                ->where(['email' => $this->email])
                ->andWhere(['!=', 'codice_fiscale', $this->codice_fiscale])
                ->exists();
            if ($exists) {
                $this->addError($attribute, 'Questa email è già associata ad un altro account.');
            }
        }
    }

    public function registra()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Cerca o crea allievo
            $allievo = Allievo::findOne(['codice_fiscale' => $this->codice_fiscale]);
            if (!$allievo) {
                $allievo = new Allievo();
                $allievo->codice_fiscale = $this->codice_fiscale;
            }

            $allievo->cognome = $this->cognome;
            $allievo->nome = $this->nome;
            $allievo->email = $this->email;
            $allievo->telefono = $this->telefono;
            $allievo->data_nascita = $this->data_nascita;
            $allievo->luogo_nascita = $this->luogo_nascita;
            $allievo->indirizzo = $this->indirizzo;
            $allievo->cap = $this->cap;
            $allievo->citta = $this->citta;
            $allievo->provincia = $this->provincia;

            if (!$allievo->save()) {
                throw new \Exception('Errore nel salvataggio del profilo.');
            }

            // Crea auth
            $auth = new AllievoAuth();
            $auth->allievo_codice_fiscale = $this->codice_fiscale;
            $auth->setPassword($this->password);
            $auth->generateAuthKey();
            $auth->status = AllievoAuth::STATUS_ACTIVE; // Attivo subito (no verifica email per ora)
            $auth->email_verified_at = date('Y-m-d H:i:s');

            if (!$auth->save()) {
                throw new \Exception('Errore nella creazione delle credenziali.');
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }
}
